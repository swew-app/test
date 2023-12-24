<?php

declare(strict_types=1);

namespace Swew\Test\CliCommands;

use LogicException;
use Swew\Cli\Command;
use Swew\Test\TestMaster;
use Swew\Test\Utils\CliStr;

class LoadConfig extends Command
{
    public const NAME = 'config
        {--init= (bool): Create new config file}
        {--config|-c= (str): Path to config file}
        {--dir= (str): Directory to scan for the test files}
        ';

    public const DESCRIPTION = 'Configuration manager';

    public function __invoke(): int
    {
        $commander = $this->getCommander();

        if (!($commander instanceof TestMaster)) {
            throw new LogicException('Is not testMaster');
        }

        if (!($this->output)) {
            throw new LogicException('Empty output');
        }

        // Create config if not exists
        $needCreateConfig = $this->argv('init');
        if ($needCreateConfig) {
            return $this->createNewConfigFile($commander->config);
        }

        // Search root dir
        $dirPath = $this->argv('dir');
        $commander->config['_root'] = $this->getRootPath($dirPath);

        // Search config file
        $configPath = $this->argv('config') ?: 'swew.json';
        $configFile = $this->getRootPath('', $configPath) . $configPath;

        $this->updateConfig($configFile, $commander->config);

        $color = $commander->config['log']['color'];
        $this->output->setAnsi($color);

        // Устанавливаем Output что бы был один объект для вывода
        CliStr::vm()->setOutput($this->output);

        return self::SUCCESS;
    }

    private function createNewConfigFile(array $configData): int
    {
        $root = $this->getRootPath();

        if (empty($root)) {
            $this->output?->error("CONFIG: Can't find root dir with composer.json");
            return self::ERROR;
        }

        $configFile = $root . 'swew.json';

        $config = [
            'test' => array_filter(
                $configData,
                fn($key) => !str_starts_with($key, '_'),
                ARRAY_FILTER_USE_KEY
            ),
        ];

        if (file_exists($configFile)) {
            $json = json_decode(file_get_contents($configFile), true);
            $config = array_merge($json, $config);
        }

        $this->writeJsonFile($configFile, $config);

        $this->output?->info("File: '$configFile' created");

        $this->addScriptToComposerJson();

        return self::SUCCESS;
    }

    private function addScriptToComposerJson(): void
    {
        $answer = $this->output?->askYesNo('Add script "test" to composer.json?');

        if (empty($answer)) {
            return;
        }

        $root = $this->getRootPath();

        $composerFile = realpath($root . 'composer.json');

        $json = json_decode(file_get_contents($composerFile), true);

        if (empty($json['scripts'])) {
            $json['scripts'] = [];
        }

        $json['scripts']['test'] = 't';

        $this->writeJsonFile($composerFile, $json);

        $this->output?->info('script "test" added to composer.json');
    }

    private function getRootPath(string $dirArg = '', string $searchFile = 'composer.json'): string
    {
        if (!empty($dirArg)) {
            if (str_starts_with($dirArg, '/')) {
                return $dirArg;
            }
            // getcwd()
            return realpath($_SERVER['PWD'] . DIRECTORY_SEPARATOR . $dirArg);
        }

        $dirs = explode(DIRECTORY_SEPARATOR, __DIR__);

        $i = count($dirs) + 1;

        while ($i--) {
            array_splice($dirs, $i);
            $path = implode(
                DIRECTORY_SEPARATOR,
                $dirs
            );

            if ($path === '') {
                break;
            }

            $composerFile = $path . DIRECTORY_SEPARATOR . $searchFile;

            if (file_exists($composerFile)) {
                return $path . DIRECTORY_SEPARATOR;
            }
        }

        return '';
    }

    private function updateConfig(string $configFile, array &$defaultConfig): void
    {
        if (!file_exists($configFile)) {
            $errorMessage = "<bgRed> CONFIG: Can't find config file: '$configFile' </>" . PHP_EOL . PHP_EOL .
                '  <b>Try creating a new config by adding the</> <yellow>--init</>' . PHP_EOL . PHP_EOL .
                '<b>example:</>' . PHP_EOL .
                '  <yellow>composer exec t -- --init</>' . PHP_EOL . PHP_EOL;

            $this->output?->writeLn($errorMessage);
            exit(self::ERROR);
        }

        $json = json_decode(file_get_contents($configFile), true);

        $this->checkKey('test', $json, $configFile);
        $config = $json['test'];

        $this->checkKey('paths', $config, $configFile);

        $newConfig = [
            'paths' => $config['paths'] ?? $defaultConfig['paths'],
            'bail' => $config['bail'] ?? $defaultConfig['bail'],
            'preloadFile' => $config['preloadFile'] ?? $defaultConfig['preloadFile'],
            'log' => array_merge(
                $defaultConfig['log'],
                $config['log'] ?? []
            ),
        ];

        $defaultConfig = array_merge($defaultConfig, $newConfig);
    }

    private function checkKey(string $key, array $arr, string $file): void
    {
        if (!array_key_exists($key, $arr)) {
            $this->output?->error("Key '$key' not found in file $file");
            exit(self::ERROR);
        }
    }

    public function init(): void
    {
        if (empty(getenv('__TEST__'))) {
            putenv('__TEST__=true');
        }
    }

    private function writeJsonFile(string $filePath, array $json): void
    {
        $jsonStr = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $file = fopen($filePath, 'w') or die("Unable to open file '$filePath'!");
        fwrite($file, $jsonStr);
        fclose($file);
    }
}