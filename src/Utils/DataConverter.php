<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

use Swew\Test\LogMaster\Log\LogData;

final class DataConverter
{
    private function __construct()
    {
    }


    public static function getMessage(LogData $item, bool $isError = false): string
    {
        $width = CliStr::vm()->width() - 24;

        if (strlen($item->message) > $width - 4) {
            $msg = str_pad(mb_substr( $item->message, 0, $width - 3) . '<gray>...</>', $width, ' ');
        } else {
            $msg = str_pad($item->message, $width, ' ');
        }


        if ($isError) {
            return "<red>$msg</>";
        }

        return $msg;
    }


    public static function getIcon(LogData $item): string
    {
        return match (true) {
            $item->isSkip => '<gray>-</>',
            $item->isTodo => '<yellow>!</>',
            $item->isExcepted => '<red>✘</>',
            default => '<green>✓</>',
        };
    }

    public static function getTime(float|int $time): string
    {
        $len = strlen(strval(intval($time)));

        if ($len > 1) {
            $decimals = 0;
        } else {
            $decimals = 4;
        }

        $isMs = $time < 0.001;

        if ($isMs) {
            $time = $time * 1000;
        }

        $val = substr(number_format($time, $decimals), 0, 8);

        return str_pad($val, 8, ' ', STR_PAD_LEFT) . ($isMs ? '<gray>ms</>' : '<gray> s</>');
    }

    public static function memorySize(int $size): string
    {
        $units = array('b ', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
        $power = $size > 0 ? intval(log($size, 1024)) : 0;

        $unit = '<gray>' . $units[$power] . '</>';
        $val = number_format(
            $size / pow(1024, $power),
            1,
            '.',
            '\''
        );

        $val = str_pad($val, 7, ' ', STR_PAD_LEFT);

        return "$val$unit";
    }

    public static function parseTraceItem(array $v): string
    {
        $fileLine = CliStr::vm()->trimPath($v['file']) . ':' . $v['line'];
        $width = CliStr::vm()->width() - 2;
        $fileLine = str_pad($fileLine, $width, ' ');

        $methodLine = '';

        if (in_array('class', $v)) {
            $methodLine .= $v['class'];
        }
        if (in_array('type', $v)) {
            $methodLine .= $v['type'];
        }
        if (in_array('function', $v)) {
            $methodLine .= $v['function'] . "(...)";
        }

        $params = [];
        if (isset($v['args']) && count($v['args']) > 0) {
            $params[] = '<bgYellow> ' . str_pad('Arguments passed', $width, ' ', STR_PAD_BOTH) . '</>';
            foreach ($v['args'] as $param) {
                $params[] = '<yellow> ❯ </>' . print_r($param, true);
            }
        }
        $params[] = '';

        return "<red>❯</> $fileLine </>\n"
            . self::getContentByLine($v['file'], $v['line'])
            . "\n"
            . CliStr::vm()->getLine()
            . $methodLine
            . implode("\n", $params)
            . "</>";
    }


    private static function getContentByLine(string $filePath, int $line = 0): string
    {
        $len = 10;
        $start = max($line - ($len / 2), 0);

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $lines = array_slice($lines, $start, $len - 3, true);

        foreach ($lines as $i => &$v) {
            ++$start;
            $v = str_pad("<bgGray>$start:</>", 3, ' ', STR_PAD_LEFT)
                . ' '
                . ($start === $line ? "<red>$v</>" : $v);
        }

        return implode("\n", $lines);
    }
}
