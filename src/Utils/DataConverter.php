<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

use Error;
use Exception;
use Swew\Test\LogMaster\Log\LogData;
use Throwable;

final class DataConverter
{
    private function __construct()
    {
    }

    public static function getIcon(LogData $item): string
    {
        return match (true) {
            $item->isSkip => '<gray>￬</>',
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

        return str_pad($val, 8, ' ', STR_PAD_LEFT) . ($isMs ? '<gray>ms</>' : '<gray>s</>');
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

    public static function getParsedException(Exception|Error|Throwable $exception, string $msg = ''): string
    {
        $msg = "\n\n" . CliStr::vm()->getLine($msg, '<red>');

        $trace = $exception->getTrace();

        $trace = array_reverse($trace);

        foreach ($trace as $t) {
            $msg .= DataConverter::parseTraceItem($t);
        }

        $msg .= PHP_EOL . "<bgYellow> </>" . PHP_EOL;
        $msg .= "<bgYellow> </> <bgRed> " . trim($exception->getMessage(), PHP_EOL) . "</>";
        $msg .= PHP_EOL . "<bgYellow> </>" . PHP_EOL . PHP_EOL;

        return $msg;
    }

    public static function parseTraceItem(array $v): string
    {
        $fileLine = CliStr::vm()->trimPath($v['file']) . ':' . $v['line'];
        $width = CliStr::vm()->width();

        $methodLine = '';

        if (isset($v['class'])) {
            $methodLine .= $v['class'];
        }
        if (isset($v['type'])) {
            $methodLine .= $v['type'];
        }
        if (isset($v['function'])) {
            $methodLine .= $v['function'] . "(...)";
        }

        if (!empty($methodLine)) {
            $methodLine = '<bgBlue> ' . str_pad($methodLine, $width, ' ') . '</>' . PHP_EOL;
        }

        $params = [];
        if (isset($v['args']) && count($v['args']) > 0) {
            $params[] = '<bgPurple> ' . str_pad('Passed arguments', $width, ' ', STR_PAD_BOTH) . '</>';
            foreach ($v['args'] as $param) {
                $params[] = '<bgPurple> </><purple> ❯ </>' . print_r($param, true);
            }
            $params[] = '<bgPurple> </>';
            $params[] = '';
        }

        return PHP_EOL . "<red>❯</> $fileLine </>" . PHP_EOL
            . $methodLine
            . implode(PHP_EOL, $params)
            . self::getContentByLine($v['file'], $v['line'])
            . PHP_EOL
            . "</>";
    }

    private static function getContentByLine(string $filePath, int $line): string
    {
        $len = 8;
        $start = max($line - ($len / 2), 0);

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $lines = array_slice($lines, $start, $len - 3, true);

        foreach ($lines as $i => &$v) {
            ++$start;
            $bgNum = $start === $line ? '<bgRed>' : '<bgGray>';
            $v = "{$bgNum}{$start}:</> " . ($start === $line ? "<red>$v</>" : $v);
        }

        return implode("\n", $lines);
    }

    public static function getMessage(LogData $item, bool $isError = false): string
    {
        $width = CliStr::vm()->width() - 24;

        if (strlen($item->message) > $width - 4) {
            $msg = str_pad(mb_substr($item->message, 0, $width - 1) . '<gray>…</>', $width, ' ');
        } else {
            $msg = str_pad($item->message, $width, ' ');
        }


        if ($isError) {
            return "<red>$msg</>";
        }

        return $msg;
    }
}
