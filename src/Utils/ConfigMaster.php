<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

use Swew\Test\Exceptions\Exception;

final class ConfigMaster
{
    private static array $config = [
        'preloadFile' => '',
        'paths' => ['**/*.spec.php', '**/*.test.php'],
        'bail' => false,
        'log' => [
            'traceReverse' => true,
            'logo' => true,
            'color' => true,
            'clear' => true,
            'short' => false,
        ],
    ];

    private static string $preloadFile = '';

    private function __construct()
    {
    }

    public static function createConfigFile(): string
    {
        $root = self::getRootPath();

        if (empty($root)) {
            throw new Exception("CONFIG: Can't find root dir with composer.json");
        }

        $configFile = $root . 'swew.json';
        $config = [
            'test' => self::$config,
        ];

        if (file_exists($configFile)) {
            $json = json_decode(file_get_contents($configFile), true);
            $config = array_merge($json, $config);
        }

        $json = json_encode($config, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

        $file = fopen($configFile, 'w') or die("Unable to open file!");
        fwrite($file, $json);
        fclose($file);

        return $configFile;
    }

    public static function addTestScriptToComposer(): bool
    {
        $root = self::getRootPath();

        if (empty($root)) {
            throw new Exception("CONFIG: Can't find root dir with composer.json");
        }

        $composerFile = $root . 'composer.json';

        if (!file_exists($composerFile)) {
            throw new Exception("CONFIG: Can't find file '$composerFile'");
        }

        $json = json_decode(file_get_contents($composerFile), true);

        if (empty($json['scripts'])) {
            $json['scripts'] = [];
        }

        $json['scripts']['test'] = 't';

        $composerContent = json_encode($json, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

        return !!file_put_contents($composerFile, $composerContent);
    }

    public static function getRootPath(): string
    {
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

            $composerFile = $path . DIRECTORY_SEPARATOR . 'composer.json';

            if (file_exists($composerFile)) {
                $json = json_decode(file_get_contents($composerFile), true);

                if ($json['name'] === 'swew/test') {
                    continue;
                }

                return $path . DIRECTORY_SEPARATOR;
            }
        }

        return '';
    }

    public static function getConfig(string $key = ''): array|string|bool
    {
        if (str_contains($key, '.')) {
            $conf = &self::$config;

            foreach (explode('.', $key) as $k) {
                $conf = &$conf[$k];
            }

            return $conf;
        }

        if (!empty($key)) {
            return self::$config[$key];
        }

        return self::$config;
    }

    public static function setConfig(string $key, array|bool|string $val): void
    {
        if (str_contains($key, '.')) {
            $conf = &self::$config;

            foreach (explode('.', $key) as $k) {
                $conf = &$conf[$k];
            }
            $conf = $val;

            return;
        }

        self::$config[$key] = $val;
    }

    public static function loadConfig(): string|null
    {
        if (CliArgs::hasArg('config')) {
            $configFile = (string)CliArgs::val('config');

            if (!file_exists($configFile)) {
                return "<bgRed> CONFIG: Can't find config file: '$configFile' </>";
            }
        } else {
            $configFile = getcwd() . DIRECTORY_SEPARATOR . 'swew.json';

            if (!file_exists($configFile)) {
                $configFile = self::getRootPath() . 'swew.json';
            }

            if (!file_exists($configFile)) {
                return "<bgRed> CONFIG: Can't find config file: '$configFile' </>" . PHP_EOL . PHP_EOL .
                    '  <b>Try creating a new config by adding the</> <yellow>--init</>' . PHP_EOL . PHP_EOL .
                    '<b>example:</>' . PHP_EOL .
                    '  <yellow>composer exec t -- --init</>' . PHP_EOL . PHP_EOL;
            }
        }

        $json = file_get_contents($configFile);

        if (!$json) {
            throw new Exception("CONFIG: Can't load config file: '$configFile'");
        }

        $config = json_decode($json, true);

        if (isset($config['test'])) {
            $config = $config['test'];
        }

        self::checkConfigValidation($config);

        self::setConfig('paths', $config['paths']);

        if (is_array($config['log'])) {
            $log = (array)self::getConfig('log');
            $log = array_merge($log, $config['log']);

            self::setConfig('log', $log);
        }

        if (is_bool($config['bail'])) {
            self::setConfig('bail', $config['bail']);
        }

        if (!empty($config['preloadFile'])) {
            self::setConfig('preloadFile', $config['preloadFile']);

            if (!file_exists($config['preloadFile'])) {
                $preload = self::getRootPath() . DIRECTORY_SEPARATOR . $config['preloadFile'];

                if (!file_exists($preload)) {
                    throw new Exception("CONFIG: Can't load preloadFile file: '$preload'");
                }

                self::$preloadFile = $preload;
            } else {
                self::$preloadFile = $config['preloadFile'];
            }
        }

        return null;
    }

    private static function checkConfigValidation(array $config): void
    {
        if (empty($config['paths'])) {
            throw new Exception("CONFIG: 'paths' - is required parameter in config file.");
        }
    }
}
