<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

final class CliStr
{
    private static array $colors = [
        'off' => "\033[0m",
        'black' => "\033[30m",
        'b' => "\033[30m",
        'grey' => "\033[90m",
        'red' => "\033[31m",
        'r' => "\033[31m",
        'green' => "\033[32m",
        'g' => "\033[32m",
        'cyan' => "\033[36m",
        'c' => "\033[36m",
        'yellow' => "\033[33m",
        'y' => "\033[33m",
        'white' => "\033[37m",
        'w' => "\033[37m",
        'R' => "\033[30m\033[41m",
        'Rw' => "\033[38m\033[41m",
        'G' => "\033[30m\033[42m",
        'Y' => "\033[30m\033[43m",
        'B' => "\033[30m\033[44m",
        'RL' => "\033[30m\033[41m \033[0m",
        'GL' => "\033[30m\033[42m \033[0m",
        'YL' => "\033[30m\033[43m \033[0m",
        'Ur' => "\e[4m\033[31m",
        'Ug' => "\e[4m\033[32m",
    ];

    private static bool $hasColor = true;

    private function __construct()
    {
    }

    public static function write(string|array $line): void
    {
        if (is_array($line)) {
            $line = implode("\n", $line);
        }

        echo $line;
    }

    public static function withColor(bool $hasColor): void
    {
        self::$hasColor = $hasColor;
    }

    public static function cl(string $color, string $m = '', bool $close = true): string
    {
        if (self::$hasColor === false) {
            return $m;
        }

        return self::$colors[$color] . $m . ($close ? self::$colors['off'] : '');
    }


    public static function line(string $color = '', bool $nl = false, string $line = '─  '): string
    {
        $line = str_pad('', 80, $line);

        if ($nl) {
            $line .= "\n";
        }

        if ($color === '') {
            return $line;
        }

        return CliStr::cl($color, $line);
    }

    private static string $rootPath = '';

    public static function setRootPath(string $rootPath): void
    {
        self::$rootPath = $rootPath;
    }

    public static function trimPath(string $str): string
    {
        return str_replace(
            rtrim(self::$rootPath, DIRECTORY_SEPARATOR),
            '',
            $str
        );
    }

    public static function clear(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            self::write("\x1B[2J\x1B[0f");
        } else {
            self::write("\x1B[2J\x1B[3J\x1B[H");
        }
    }

    public static function clearColor(string $str): string
    {
        $patterns = '/\033\[\d+m/';

        return (string)preg_replace($patterns, '', $str);
    }
}
