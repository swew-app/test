<?php

declare(strict_types=1);

namespace SWEW\Test\Utils;

use SWEW\Test\Exceptions\Exception;

final class ConfigMaster
{
    private static array $config = [
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

    private function __construct()
    {
    }

    public static function createConfigFile(): string
    {
        $root = self::getRootPath();

        if (empty($root)) {
            throw new Exception("Can't find root dir with composer.json");
        }

        $configFile = $root . DIRECTORY_SEPARATOR . 'swew-test.json';
        $json = json_encode(self::$config, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

        $file = fopen($configFile, 'w') or die("Unable to open file!");
        fwrite($file, $json);
        fclose($file);

        return $configFile;
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

            if (file_exists($path . DIRECTORY_SEPARATOR . 'composer.json')) {
                return $path;
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

    public static function setConfig(string $key, array|bool $val): void
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

    public static function loadConfig(): void
    {
        if (CliArgs::hasArg('config')) {
            $configFile = (string)CliArgs::val('config');


            if (!file_exists($configFile)) {
                throw new Exception("Can't find config file: '$configFile'");
            }
        } else {
            $configFile = getcwd() . DIRECTORY_SEPARATOR . 'swew-test.json';

            if (!file_exists($configFile)) {
                $configFile = self::getRootPath() . DIRECTORY_SEPARATOR . 'swew-test.json';
            }

            if (!file_exists($configFile)) {
                CliStr::write(
                    "\n"
                    . CliStr::cl('B', '  Try creating a new config by adding the ', false)
                    . CliStr::cl('yellow', '--init', false)
                    . CliStr::cl('B', ' argument  ')
                    . "\n\n"
                );

                throw new Exception("Can't find config file: '$configFile'");
            }
        }

        $json = file_get_contents($configFile);

        if (!$json) {
            throw new Exception("Can't load config file: '$configFile'");
        }

        $config = json_decode($json, true);

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
    }

    private static function checkConfigValidation(array $config): void
    {
        if (empty($config['paths'])) {
            throw new Exception("'paths' - is required parameter in config file.");
        }
    }
}
