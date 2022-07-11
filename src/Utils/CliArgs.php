<?php

declare(strict_types=1);

namespace SWEW\Test\Utils;


use SWEW\Test\Exceptions\Exception;

final class CliArgs
{
    private static array $argv = [];

    private static array $options = [];

    private static array $parsedOptions = [];

    private function __construct()
    {
    }

    public static function init(array $argv = [], array $options = []): void
    {
        if (count($argv) === 0) {
            $argv = $_SERVER["argv"];
        }

        self::$argv = $argv;

        self::$options = [
                'help,h' => [
                    'desc' => 'Show help'
                ]
            ] + $options;

        self::parseOptions(self::$options);
    }

    public static function val(string $key): string|bool
    {
        $res = self::findArg($key);

        if (is_null($res)) {
            throw new Exception('Unknown argument');
        }

        return $res ?: false;
    }

    private static function parseOptions(array $options): void
    {
        $keysRaw = array_keys($options);

        foreach ($keysRaw as $k) {
            $listOfKeys = explode(',', $k);

            foreach ($listOfKeys as $i => $kk) {
                self::$parsedOptions[$kk] = $options[$k] + [
                        'keys' => $listOfKeys,
                    ];
            }
        }
    }

    private static function findArg(string $key): string|bool|null
    {
        foreach (self::$argv as $i => $arg) {
            if ($arg[0] === '-') {
                $name = ltrim($arg, '-');

                if (array_key_exists($name, self::$parsedOptions)) {
                    if (in_array($key, self::$parsedOptions[$name]['keys'])) {
                        // key is found, check the value
                        $nexIndex = intval($i) + 1;

                        if (count(self::$argv) > $nexIndex) {
                            return self::$argv[$nexIndex];
                        }

                        return true;
                    }
                }
            }
        }

        return null;
    }

    public static function getHelp(): string
    {
        $help = [''];
        $help[] = 'Help information.';
        $help[] = '';

        foreach (self::$options as $k => $v) {
            $keys = explode(',', $k);
            $str = [];

            foreach ($keys as $key) {
                $str[] = CliStr::cl('c', '-' . $key);
            }

            $help[] = ' ' . implode(', ', $str) . ":\t" . $v['desc'];
        }

        $help[] = '';

        return implode("\n", $help);
    }
}
