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

        return $val . ($isMs ? '<gray>ms</>' : '<gray>s </>');
    }

    public static function formatMicrotime(float $microtime): string
    {
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

        $width = CliStr::vm()->width() - 1;

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

        $delimiterWidth = 2 + $width - strlen(strip_tags($icon)) - strlen(strip_tags($msg)) - strlen(strip_tags($right));

        if ($delimiterWidth > 4) {
            $delimiter = str_pad(' ', $delimiterWidth, '.');
        }

        if ($isError) {
            $msg = '<red>' . $msg . '</>';
        }

        return $icon . $msg . '<gray>' . $delimiter . '</>' . $right;
    }

    public static function getExceptionTraceLine(?array $item): string
    {
        if (empty($item)) {
            return '';
        }
        $file = $item['file'];
        $line = $item['line'];

        return CliStr::vm()->trimPath("$file:$line");
    }

    public static function getParsedException(Exception|Error|Throwable $exception, string $msg = ''): string
    {
        $msg = PHP_EOL . PHP_EOL . CliStr::vm()->getLine($msg, '<red>');

        $trace = array_reverse($exception->getTrace());

        foreach ($trace as $t) {
            $msg .= DataConverter::parseTraceItem($t);
        }

        $msg .= '<red>❯❯</> ' .$exception->getFile() . ':' . $exception->getLine() . PHP_EOL;
        $msg .= self::getContentByLine($exception->getFile(), $exception->getLine());

        $msg .= "<br><br><bgYellow> </> <bgRed> " . trim($exception->getMessage(), PHP_EOL) . '</><br>';

        return $msg;
    }
}
