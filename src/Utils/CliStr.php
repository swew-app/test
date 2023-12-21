<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

use Swew\Cli\Terminal\Output;

final class CliStr
{
    public readonly Output $output;

    private static ?CliStr $instance = null;

    private function __construct()
    {
        $this->output = new Output();

        self::$instance = $this;
    }

    public static function vm(): self
    {
        if (!is_null(self::$instance)) {
            return self::$instance;
        }

        self::$instance = new self();

        return self::$instance;
    }

    private int $widthSize = 0;

    public function width(): int
    {
        if ($this->widthSize) {
            return $this->widthSize;
        }
        return $this->widthSize = max($this->output->width(), 60);
    }

    public function write(string|array $line): void
    {
        if (is_array($line)) {
            $line = implode(PHP_EOL, $line);
        }

        $this->output->write($line);
    }

    public function withColor(bool $hasColor): void
    {
        $this->output->setAnsi($hasColor);
    }

    public function getWithPrefix(string $text, bool $isGood): string
    {
        $lines = explode("\n", $text);
        $color = $isGood ? '<bgGreen> </> ' : '<bgRed> </> ';

        foreach ($lines as &$line) {
            $line = $color . $line;
        }

        return implode("\n", $lines);
    }

    public function getLine(string $message = '', string $color = '<gray>'): string
    {
        $width = $this->width() - 1;

        return $color . ' ' . str_pad($message, $width, '-', STR_PAD_BOTH) . "</>";
    }

    private static string $rootPath = '';

    public static function setRootPath(string $rootPath): void
    {
        self::$rootPath = $rootPath;
    }

    /**
     * Trim file path to root of project
     *
     * @param string $str
     * @return string
     */
    public function trimPath(string $str): string
    {
        if (empty(self::$rootPath)) {
            return $str;
        }

        $str = str_replace(
            self::$rootPath,
            '',
            $str
        );

        return ltrim($str, DIRECTORY_SEPARATOR);
    }

    /**
     * Clear terminal
     *
     * @return void
     */
    public function clear(): void
    {
        $this->output->clear();
    }

    /**
     * Remove bash color symbols from string
     *
     * @param string $str
     * @return string
     */
    public static function clearColor(string $str): string
    {
        $patterns = "/\e?\[[\d;]+m/";

        return (string)preg_replace($patterns, '', $str);
    }
}
