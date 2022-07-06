<?php

declare(strict_types=1);

namespace SWEW\Test\Utils;

use SWEW\Test\LogMaster\Log\LogData;

final class DataConverter
{
    private function __construct()
    {
    }


    public static function getMessage(LogData $item, bool $isError = false): string
    {
        $msg = str_pad($item->message, 50, ' ');

        if ($isError) {
            return CliStr::cl('red', $msg);
        }

        return $msg;
    }


    public static function getIcon(LogData $item): string
    {
        return match (true) {
            $item->isSkip => CliStr::cl('grey', '-'),
            $item->isTodo => CliStr::cl('yellow', '!'),
            $item->isExcepted => CliStr::cl('red', '✘'),
            default => CliStr::cl('green', '✓'),
        };
    }

    public static function getTime(float|int $time): string
    {
        return number_format($time, 6) . CliStr::cl('grey', ' s');
    }

    public static function memorySize(int $size): string
    {
        $units = array('b ', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
        $power = $size > 0 ? intval(log($size, 1024)) : 0;

        $unit = CliStr::cl('grey', $units[$power]);
        $val = number_format(
            $size / pow(1024, $power),
            1,
            '.',
            '\''
        );

        $val = str_pad($val, 7, ' ', STR_PAD_LEFT);

        return "$val $unit";
    }

    public static function parseTraceItem(array $v): string
    {
        $fileLine = CliStr::cl('b', $v['file'], false)
            . CliStr::cl('w', ':' . $v['line'], false);

        return CliStr::cl('R', "  " . $fileLine . "\t")
            . "\n"
            . self::getContentByLine($v['file'], $v['line'])
            . "\n"
            . CliStr::line('grey', true)
            . CliStr::cl('c', $v['class'] . $v['type'] . $v['function'] . "(...)\n", false)
            . (count($v['args']) ? print_r($v['args'], true) : '')
            . "\n";
    }


    private static function getContentByLine(string $filePath, int $line = 0): string
    {
        $len = 10;
        $start = max($line - ($len / 2), 0);

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $lines = array_slice($lines, $start, $len, true);

        foreach ($lines as $i => &$v) {
            ++$start;
            $v = CliStr::cl('grey', str_pad("$start:", 3, ' ', STR_PAD_LEFT))
                . ' '
                . ($start === $line ? CliStr::cl('r', $v) : $v);
        }

        return implode("\n", $lines);
    }
}
