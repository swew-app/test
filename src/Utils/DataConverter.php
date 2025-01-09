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
            $item->isTodo => '<yellow>✎</>',
            $item->isExcepted => '<red>✘</>',
            default => '<green>✓</>',
        };
    }

    public static function getTime(float $time): string
    {
        // Определяем пороги для разных единиц измерения
        if ($time >= 1) {
            // Секунды: >= 1
            return number_format($time, 2, '.', '') . '<gray>s</>';
        } elseif ($time >= 0.001) {
            // Миллисекунды: >= 0.001 (1 ms)
            return number_format($time * 1000, 2, '.', '') . '<gray>ms</>';
        }
        // Микросекунды: < 0.001
        return number_format($time * 1000000, 2, '.', '') . '<gray>µs</>';
    }

    public static function formatMicrotime(float $microtime): string
    {
        if ($microtime < 0) {
            $microtime *= -1;
        }
        $seconds = (int) $microtime; // Получаем число полных секунд
        $microseconds = $microtime - $seconds; // Получаем дробную часть (миллисекунды)
        $minutes = (int) ($seconds / 60); // Конвертируем секунды в минуты
        $remainingSeconds = $seconds % 60; // Определяем оставшиеся секунды после вычисления минут

        // Переводим микросекунды в миллисекунды и форматируем вывод
        $formattedMicroseconds = sprintf("%03d", (int) round($microseconds * 1000));

        // Форматируем вывод, добавляя нули ведущие нули для минут и секунд при необходимости
        return sprintf("%02d:%02d.%s", $minutes, $remainingSeconds, $formattedMicroseconds);
    }

    public static function memorySize(int $size): string
    {
        $units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
        $power = $size > 0 ? intval(log($size, 1024)) : 0;

        $unit = '<gray>' . $units[$power] . '</>';
        $val = number_format(
            $size / pow(1024, $power),
            1,
            '.',
            '\''
        );

        return "$val$unit";
    }

    public static function parseTraceItem(array $v): string
    {
        $fileLine = self::getExceptionTraceLine($v);

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
                $params[] = '<bgPurple> </><purple> ❯ </>' . self::highlightSting(print_r($param, true));
            }
            $params[] = '<bgPurple> </>';
            $params[] = '';
        }

        return PHP_EOL . "<red>❯</> $fileLine </>" . PHP_EOL
            . $methodLine
            . implode(PHP_EOL, $params)
            . (empty($v['line']) ? '' : self::getContentByLine($v['file'], $v['line']))
            . PHP_EOL
            . "</>";
    }

    public static function highlightSting(string $text)
    {
        // reduce spaces
        $text = preg_replace('/[ ]{2}/sm', ' ', $text);

        // Array (...) => [ ]
        $text = preg_replace('/Array\n\s*\(([^\)\S]*)\)/sm', '[$1]', $text);
        $text = preg_replace('/Array\n\s*\(([^\)]*)\)/sm', '[$1]', $text);
        $text = preg_replace('/\s*\[\s+\]\s/sm', ' [ ]', $text);

        // Object
        $text = preg_replace('/(\sClosure Object)\s*\(\s*\)/sm', '<blue>Closure Object ()</>', $text);
        $text = preg_replace('/(\sClosure Object\s)/sm', '<blue>$1</>', $text);
        $text = preg_replace('/\sObject\s+\(/sm', ' Object (', $text);
        $text = preg_replace('/\s(Object)\s+(\*RECURSION\*)/sm', ' <red>$1 $2</>', $text);
        $text = preg_replace('/\s(Resource id #\d+)/sm', ' <green>$1</>', $text);

        // =>
        $text = preg_replace('/(=>)/mi', '<gray>$1</>', $text);
        // [key]
        $text = preg_replace('/(\[)([^\]]*)(\])/sm', '<red>$1<green>$2<red>$3</>', $text);

        $text = trim($text);

        return $text;
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
            $v = "{$bgNum}{$start}:</> " . ($start === $line ? "<red>$v</>" : "<gray>$v</>");
        }

        return implode("\n", $lines);
    }

    public static function getMessage(LogData $item): string
    {
        $width = CliStr::vm()->width() - 24;

        if (strlen($item->message) > $width) {
            return mb_substr($item->message, 0, $width) . '…';
        } else {
            return $item->message;
        }
    }

    public static function getTestSuiteLine(LogData $item, bool $isError = false): string
    {
        $icon = self::getIcon($item) . ' ';
        $msg = self::getMessage($item);
        $delimiter = ' ';
        $right = ' ' . self::memorySize($item->memoryUsage) . '  ' . self::getTime($item->timeUsage);
        $width = CliStr::vm()->width();

        $delimiterWidth = $width
                            - mb_strlen(strip_tags($icon), 'UTF-8')
                            - mb_strlen(strip_tags($msg), 'UTF-8')
                            - mb_strlen(strip_tags($right), 'UTF-8');

        if ($delimiterWidth > 2) {
            $delimiter = str_pad(' ', $delimiterWidth, '.');
        }

        if ($isError) {
            $msg = '<red>' . $msg . '</>';
        }

        return $icon . $msg . '<gray>' . $delimiter . '</>' . $right;
    }

    public static function getExceptionTraceLine(?array $item): string
    {
        if (empty($item) || empty($item['file'])) {
            return '';
        }
        $file = $item['file'];
        $line = $item['line'];

        return CliStr::vm()->trimPath("$file:$line");
    }

    public static function getParsedException(Exception|Error|Throwable $exception, string $msg = ''): string
    {
        $text = PHP_EOL . PHP_EOL . CliStr::vm()->getLine($msg, '<red>');

        $trace = array_reverse($exception->getTrace());

        foreach ($trace as $t) {
            if (
                isset($t['file']) &&
                (
                    str_contains($t['file'], 'vendor/swew/test') ||
                    str_contains($t['file'], 'swew/test/src') ||
                    str_contains($t['file'], 'swew/test/bin') ||
                    str_contains($t['file'], 'vendor/bin/t')
                )
            ) {
                continue;
            }
            $text .= DataConverter::parseTraceItem($t);
        }

        $text .= '<red>❯❯</> ' . $exception->getFile() . ':' . $exception->getLine() . PHP_EOL;
        $text .= self::getContentByLine($exception->getFile(), $exception->getLine());

        $text .= "<br><br><bgYellow> </> <bgRed> " . trim($exception->getMessage(), PHP_EOL) . '</>';
        $text .= PHP_EOL . CliStr::vm()->getLine($msg, '<red>', ' ') . PHP_EOL;

        return $text;
    }
}
