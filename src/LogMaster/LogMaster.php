<?php

declare(strict_types=1);

namespace SWEW\Test\LogMaster;

use SWEW\Test\Suite\Log\LogData;

final class LogMaster
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
        'G' => "\033[30m\033[42m",
        'Y' => "\033[30m\033[43m",
        'B' => "\033[30m\033[44m",
        'RL' => "\033[30m\033[41m \033[0m",
        'GL' => "\033[30m\033[42m \033[0m",
        'YL' => "\033[30m\033[43m \033[0m",
    ];

    public function __construct(
        private readonly array $results,
        private array          $config = [],
        private float          $testingTime = 0
    ) {
        $this->config = array_merge($config, [
            'traceReverse' => true,
        ]);
    }

    public function logList(): void
    {
        $allTests = 0;
        $excepted = 0;
        $passed = 0;
        $skipped = 0;
        $todo = 0;

        // TODO: $hasOnly = false; // окрашивать в красный Passed

        foreach ($this->results as $r) {
            ++$allTests;
            if ($r->isExcepted) {
                ++$excepted;
                $this->echoExpectedSuite($r);
            } else {
                $this->echoSuite($r);

                if ($r->isSkip) {
                    ++$skipped;
                } elseif ($r->isTodo) {
                    ++$todo;
                } else {
                    ++$passed;
                }
            }
        }

        $maxMemory = memory_get_peak_usage();

        $passedColor = $passed > 0 ? 'green' : 'white';
        $exceptedColor = $excepted > 0 ? 'red' : 'grey';
        $skippedColor = $skipped > 0 ? 'yellow' : 'grey';
        $todoColor = $todo > 0 ? 'yellow' : 'grey';

        $allTests = str_pad("$allTests", 3, ' ', STR_PAD_LEFT);
        $passed = str_pad("$passed", 3, ' ', STR_PAD_LEFT);
        $excepted = str_pad("$excepted", 3, ' ', STR_PAD_LEFT);
        $skipped = str_pad("$skipped", 3, ' ', STR_PAD_LEFT);
        $todo = str_pad("$todo", 3, ' ', STR_PAD_LEFT);

        $passed = $this->cl($passedColor, $passed);
        $excepted = $this->cl($exceptedColor, $excepted);
        $skipped = $this->cl($skippedColor, $skipped);
        $todo = $this->cl($todoColor, $todo);

        $lines = [
            "",
            "  Tests:",
            $this->cl('grey', '   - All:     ') . $allTests,
            $this->cl('grey', '   - Passed:  ') . $passed,
            $this->cl('grey', '   - Excepted:') . $excepted,
            $this->cl('grey', '   - Skipped: ') . $skipped,
            $this->cl('grey', '   - Todo:    ') . $todo,
            " Memory: " . $this->memorySize($maxMemory),
            "   Time: " . $this->getTime($this->testingTime),
            "",
            "",
        ];

        echo implode("\n", $lines);
    }

    public function line(string $color = '', bool $nl = false): string
    {
        $line = str_pad('', 120, '─  ');

        if ($nl) {
            $line .= "\n";
        }

        if ($color === '') {
            return $line;
        }

        return $this->cl($color, $line);
    }

    public function cl(string $color, string $m = '', $close = true): string
    {
        return LogMaster::$colors[$color] . $m . ($close ? LogMaster::$colors['off'] : '');
    }


    private function echoSuite(LogData $item): void
    {
        echo ' ' . $this->getIcon($item) . ' '
            . $this->getMessage($item)
            . $this->memorySize($item->memoryUsage) . '  '
            . $this->getTime($item->timeUsage)
            . "\n";
    }

    public function echoExpectedSuite(LogData $item): void
    {
        $msg = '';

        if (!is_null($item->exception)) {
            $msg = $this->cl('RL', ' ' . $item->exception->getMessage()) . "\n"
                . $this->cl('RL', '   ' . $item->exception->getFile())
                . $this->cl('grey', ':' . $item->exception->getLine())
                . "\n"
                . $this->cl('RL')
                . "\n";

            $trace = $item->exception->getTrace();

            if ($this->config['traceReverse']) {
                $trace = array_reverse($trace);
            }

            foreach ($trace as $t) {
                $msg .= $this->parseTraceItem($t);
            }
        }

        echo ' ' . $this->getIcon($item) . ' '
        . $this->getMessage($item, true) . "\n"
        . $msg ?? $item->exception
        . "\n";
    }

    private function parseTraceItem(array $v): string
    {
        $fileLine = $this->cl('b', $v['file'], false)
            . $this->cl('w', ':' . $v['line'], false);

        return $this->cl(
            'R',
            "  " . $fileLine . "\t"
        )
            . "\n"
            . $this->getContentByLine($v['file'], $v['line'])
            . "\n"
            . $this->line('grey', true)
            . $this->cl('c', $v['class'] . $v['type'] . $v['function'] . "(...)\n", false)
            . (count($v['args']) ? print_r($v['args'], true) : '')
            . "\n";
    }

    private function getContentByLine(string $filePath, int $line = 0): string
    {
        $len = 10;
        $start = max($line - ($len / 2), 0);

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $lines = array_slice($lines, $start, $len, true);

        foreach ($lines as $i => &$v) {
            ++$start;
            $v = $this->cl('grey', str_pad("$start:", 3, ' ', STR_PAD_LEFT))
                . ' '
                . ($start === $line ? $this->cl('r', $v) : $v);
        }

        return implode("\n", $lines);
    }


    private function memorySize(int $size): string
    {
        $units = array('B ', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? intval(log($size, 1024)) : 0;

        $unit = $this->cl('grey', $units[$power]);
        $val = number_format(
            $size / pow(1024, $power),
            1,
            '.',
            '\''
        );

        $val = str_pad($val, 6, ' ', STR_PAD_LEFT);

        return  "$val $unit";
    }

    private function getMessage(LogData $item, bool $isError = false): string
    {
        $msg = str_pad($item->message, 40, ' ');

        if ($isError) {
            return $this->cl('red', $msg);
        }

        return $msg;
    }

    private function getIcon(LogData $item): string
    {
        return match (true) {
            $item->isSkip => $this->cl('grey', '.'),
            $item->isTodo => $this->cl('grey', '⋅'),
            $item->isExcepted => $this->cl('red', '✘'),
            default => $this->cl('green', '✓'),
        };
    }

    private function getTime(float|int $time): string
    {
        return number_format($time, 6) . $this->cl('grey', ' s');
    }
}
