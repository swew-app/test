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
        private array          $config = []
    ) {
        $this->config = array_merge($config, [
            'traceReverse' => true,
        ]);
    }

    public function logList(): void
    {
        foreach ($this->results as $r) {
            if ($r->isExcepted) {
                $this->echoExpectedSuite($r);
            } else {
                $this->echoSuite($r);
            }
        }

        $maxMemory = memory_get_peak_usage();

        echo 'Max memory: ' . $this->memorySize($maxMemory) . "\n";
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
            . $this->getTime($item)
            . "\n";
    }

    public function echoExpectedSuite(LogData $item): void
    {
        $msg = '';

        if (!is_null($item->exception)) {
            $msg = LogMaster::$colors['RL'] . ' ' . $item->exception->getMessage() . "\n"
                . LogMaster::$colors['RL'] . '   '
                . $item->exception->getFile() . LogMaster::$colors['grey'] . ':'
                . $item->exception->getLine() . LogMaster::$colors['off']
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
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? intval(log($size, 1024)) : 0;

        $unit = $this->cl('grey', $units[$power]);

        return number_format(
            $size / pow(1024, $power),
            1,
            '.',
            ','
        ) . ' ' . $unit;
    }

    private function getMessage(LogData $item, bool $isError = false): string
    {
        $msg = str_pad($item->message, 40, ' ');

        if ($isError) {
            return LogMaster::$colors['red'] . $msg . LogMaster::$colors['off'];
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

    private function getTime(LogData $item): string
    {
        return number_format($item->timeUsage, 5) . $this->cl('grey', ' s');
    }
}
