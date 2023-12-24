<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

use Swew\Cli\Terminal\Output;

final class CliStr
{
    private static ?CliStr $instance = null;

    private static string $rootPath = '';

    private function __construct(
        public Output $output = new Output()
    )
    {
        self::$instance = $this;
    }

    public static function vm(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function setRootPath(string $rootPath): void
    {
        self::$rootPath = $rootPath;
    }

    public function setOutput(Output $output): void
    {
        $this->output = $output;
    }

    private int $widthSize = 0;

    public function width(): int
    {
        if ($this->widthSize) {
            return $this->widthSize;
        }
        return $this->widthSize = max($this->output->width(), 60);
    }

    public function getWithPrefix(string $text, bool $isGood): string
    {
        $lines = explode(PHP_EOL, $text);
        $color = $isGood ? '<bgGreen> </> ' : '<bgRed> </> ';

        foreach ($lines as &$line) {
            $line = $color . $line;
        }

        return implode(PHP_EOL, $lines);
    }

    public function getLine(string $message = '', string $color = '<gray>'): string
    {
        $width = $this->width();

        return $color . str_pad($message, $width, '-', STR_PAD_BOTH) . "</>";
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

        $str = str_replace(self::$rootPath, '', $str);

        return ltrim($str, DIRECTORY_SEPARATOR);
    }
}
