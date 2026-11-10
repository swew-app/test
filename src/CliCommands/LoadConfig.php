<?php

declare(strict_types=1);

namespace Swew\Test\CliCommands;

use LogicException;
use Swew\Cli\Command;
use Swew\Test\Config\Config;
use Swew\Test\TestMaster;
use Swew\Test\Utils\CliStr;

class LoadConfig extends Command
{
    public const CUSTOM_SUCCESS = -1;

    public const NAME = 'config
        {--init= (bool): Create new config file}
        {--config|-c= (str): Path to config file}
        {--dir= (str): Directory to scan for the test files}
        {--no-color=false (bool): Disable color output}
        {--short|-s=false (bool): Short output}
        ';

    public const DESCRIPTION = 'Configuration manager';

    #[\Override]
    public function __invoke(): int
    {
        $commander = $this->getCommander();

        if (!$commander instanceof TestMaster) {
            throw new LogicException('Is not testMaster');
        }

        $output = $this->output;
        if ($output === null) {
            throw new LogicException('Empty output');
        }

        // Create config if not exists
        $needCreateConfig = $this->argv('init');
        if ($needCreateConfig) {
            return $this->createNewConfigFile($commander->config);
        }

        // Search root dir
        $dirPath = $this->stringOption($this->argv('dir') ?? '', 'dir');
        $commander->config->setRoot($this->getRootPath($dirPath));

        // Search config file
        $configPath = $this->stringOption($this->argv('config') ?: 'swew.json', 'config');
        $configFile = $this->getRootPath('', $configPath) . $configPath;

        $this->updateConfig($configFile, $commander->config);

        if ($this->argv('short')) {
            $commander->config->logShort = true;
        }

        if ($this->argv('no-color')) {
            $output->setAnsi(false);
        } else {
            $output->setAnsi($commander->config->logColor);
        }

        // Устанавливаем Output что бы был один объект для вывода
        CliStr::vm()->setOutput($output);
        CliStr::vm()->setRootPath($commander->config->getRoot());

        return self::SUCCESS;
    }

    private function createNewConfigFile(Config $config): int
    {
        $root = $this->getRootPath();

        if (empty($root)) {
            $this->output?->error("CONFIG: Can't find root dir with composer.json");

            return self::ERROR;
        }

        $configFile = $root . 'swew.json';

        $this->writeJsonFile($configFile, $config);

        $this->output?->info("File: '$configFile' created");

        $this->addScriptToComposerJson();

        return self::CUSTOM_SUCCESS;
    }

    private function addScriptToComposerJson(): void
    {
        $answer = $this->output?->askYesNo('Add script "test" to composer.json?');

        if (!$answer) {
            return;
        }

        $root = $this->getRootPath();

        $composerFile = $root . 'composer.json';

        $json = $this->readJsonFile($composerFile);

        $scripts = $json['scripts'] ?? [];
        if (!is_array($scripts)) {
            throw new \UnexpectedValueException('composer.json scripts must be an object.');
        }
        $scripts['test'] = 't';
        $json['scripts'] = $scripts;

        $this->writeJsonFile($composerFile, $json);

        $this->output?->info('script "test" added to composer.json');
    }

    private function getRootPath(string $dirArg = '', string $searchFile = 'composer.json'): string
    {
        if (!empty($dirArg)) {
            return $dirArg;
        }

        $cwd = getcwd();
        if ($cwd === false) {
            throw new \RuntimeException('Unable to determine the current working directory.');
        }
        $dirsForSearch = [__DIR__, $cwd];

        foreach ($dirsForSearch as $dir) {
            $dirs = explode(DIRECTORY_SEPARATOR, $dir);

            $i = count($dirs) + 1;

            while ($i--) {
                array_splice($dirs, $i);
                $path = implode(DIRECTORY_SEPARATOR, $dirs);

                if ($path === '') {
                    break;
                }

                $composerFile = $path . DIRECTORY_SEPARATOR . $searchFile;

                if (file_exists($composerFile)) {
                    if ($searchFile === 'composer.json') {
                        // Skip current package
                        $json = $this->readJsonFile($composerFile);
                        if ($json['name'] === 'swew/test' || $json['name'] === 'swew/test.phar') {
                            break;
                        }
                    }

                    return $path . DIRECTORY_SEPARATOR;
                }
            }
        }

        return '';
    }

    private function updateConfig(string $configFile, Config $conf): void
    {
        if (!file_exists($configFile)) {
            $errorMessage =
                "<bgRed> CONFIG: Can't find config file: '$configFile' </>"
                . PHP_EOL
                . PHP_EOL
                . '  <b>Try creating a new config by adding the</> <yellow>--init</>'
                . PHP_EOL
                . PHP_EOL
                . '<b>example:</>'
                . PHP_EOL
                . '  <yellow>composer exec t -- --init</>'
                . PHP_EOL
                . PHP_EOL;

            $this->output?->writeLn($errorMessage);
            exit(self::ERROR);
        }

        $json = $this->readJsonFile($configFile);

        $this->checkKey('test', $json, $configFile);
        $jsonConfig = $json['test'];
        if (!is_array($jsonConfig)) {
            throw new \UnexpectedValueException('test must be an object.');
        }
        $this->checkKey('paths', $jsonConfig, $configFile);

        $patterns = $jsonConfig['paths'] ?? $conf->paths;
        if (!is_array($patterns)) {
            throw new \UnexpectedValueException('test.paths must be an array.');
        }
        $paths = [];
        foreach ($patterns as $pattern) {
            if (!is_string($pattern) || $pattern === '') {
                throw new \UnexpectedValueException('test.paths must contain non-empty strings.');
            }
            $paths[] = $pattern;
        }
        $conf->paths = $paths;
        $conf->preloadFile = $this->stringOption($jsonConfig['preloadFile'] ?? $conf->preloadFile, 'test.preloadFile');
        $conf->bail = $this->boolOption($jsonConfig['bail'] ?? $conf->bail, 'test.bail');

        $log = $jsonConfig['log'] ?? [];
        if (!is_array($log)) {
            throw new \UnexpectedValueException('test.log must be an object.');
        }
        $conf->logLogo = $this->boolOption($log['logo'] ?? $conf->logLogo, 'test.log.logo');
        $conf->logColor = $this->boolOption($log['color'] ?? $conf->logColor, 'test.log.color');
        $conf->logClear = $this->boolOption($log['clear'] ?? $conf->logClear, 'test.log.clear');
        $conf->logShort = $this->boolOption($log['short'] ?? $conf->logShort, 'test.log.short');
        $conf->logTraceReverse = $this->boolOption(
            $log['traceReverse'] ?? $conf->logTraceReverse,
            'test.log.traceReverse'
        );

        if (!empty($jsonConfig['env'])) {
            if (!is_array($jsonConfig['env'])) {
                throw new \Exception('In the “swew.json” config, the field “test.env” - should have a key-value view');
            }

            // TODO: Добавить ключ для загрузки .env файла
            foreach ($jsonConfig['env'] as $key => $value) {
                putenv("$key=$value");
            }
        }
    }

    private function stringOption(mixed $value, string $name): string
    {
        if (!is_string($value)) {
            throw new \UnexpectedValueException("$name must be a string.");
        }

        return $value;
    }

    private function boolOption(mixed $value, string $name): bool
    {
        if (!is_bool($value)) {
            throw new \UnexpectedValueException("$name must be a boolean.");
        }

        return $value;
    }

    private function checkKey(string $key, array $arr, string $file): void
    {
        if (!array_key_exists($key, $arr)) {
            $this->output?->error("Key '$key' not found in file $file");
            exit(self::ERROR);
        }
    }

    private function readJsonFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Unable to read file '$filePath'.");
        }

        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            throw new \UnexpectedValueException("Expected a JSON object in '$filePath'.");
        }

        return $json;
    }

    private function writeJsonFile(string $filePath, array|Config $json): void
    {
        $jsonStr = json_encode(
            $json,
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        if (file_put_contents($filePath, $jsonStr) !== strlen($jsonStr)) {
            throw new \RuntimeException("Unable to write file '$filePath'.");
        }
    }
}
