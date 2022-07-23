<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

use Swew\Test\Exceptions\Exception;

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

        self::$argv = array_slice($argv, 1);

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

    public static function hasArg(string $key): bool
    {
        return !empty(self::findArg($key));
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
        $help[] = '   SWEW-test';
        $help[] = 'Help information.';
        $help[] = '';
        $help[] = 'Options:';
        $help[] = '';

        foreach (self::$options as $k => $v) {
            $keys = explode(',', $k);
            $str = [];

            foreach ($keys as $key) {
                $str[] = '-' . $key;
            }

            $help[] = str_pad(' ' . implode(', ', $str), 14, ' ')
                . ": " . $v['desc'];
        }

        $help[] = '';
        $help[] = '';

        return implode("\n", $help);
    }

    public static function hasArgs(): bool
    {
        foreach (self::$argv as $arg) {
            if ($arg[0] === '-') {
                return true;
            }
        }
        return false;
    }

    public static function hasCommand(): bool
    {
        return count(self::$argv) > 0 && self::$argv[0][0] !== '-';
    }

    public static function getCommands(): array
    {
        return self::$argv;
    }

    public static function getGlobMaskPattern(string $key = ''): ?string
    {
        if (!empty($key)) {
            if (!is_null(self::findArg($key))) {
                return '**' . self::val($key) . '*';
            }
        }

        if (self::hasArgs() === false && self::hasCommand() === true) {
            return '**' . self::getCommands()[0] . '*';
        }

        return null;
    }
}
