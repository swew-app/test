<?php

declare(strict_types=1);

namespace SWEW\Test\Runner\LogMaster;

use SWEW\Test\Cli\CliStr;
use SWEW\Test\Runner\LogMaster\Log\LogData;
use SWEW\Test\Runner\LogMaster\Log\LogState;

final class LogMaster
{
    private array $config = [];

    public function __construct(
        private readonly LogState $logState
    ) {
        if (!empty($this->logState)) {
            $this->config = array_merge(
                [
                    'traceReverse' => true,
                    'color' => true,
                ],
                $this->logState->getConfig()['log'] ?: []
            );

            CliStr::withColor($this->config['color']);
        }
    }

    public function logListAndExit(): void
    {
        $results = $this->logState->getResults();
        $allTests = $this->logState->getTestsCount();
        $hasOnly = $this->logState->hasOnlyTests();
        $excepted = 0;
        $passed = 0;
        $skipped = 0;
        $todo = 0;
        $hasExcepted = false;

        foreach ($results as $r) {
            if ($r->isExcepted) {
                ++$excepted;
                $hasExcepted = true;

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

        if ($hasOnly) {
            $passedColor = 'yellow';
        } else {
            $passedColor = $passed > 0 ? 'green' : 'white';
        }

        $exceptedColor = $excepted > 0 ? 'red' : 'grey';
        $skippedColor = $skipped > 0 ? 'yellow' : 'grey';
        $todoColor = $todo > 0 ? 'yellow' : 'grey';

        $allTests = str_pad("$allTests", 3, ' ', STR_PAD_LEFT);
        $passedStr = str_pad("$passed", 3, ' ', STR_PAD_LEFT);
        $exceptedStr = str_pad("$excepted", 3, ' ', STR_PAD_LEFT);
        $skippedStr = str_pad("$skipped", 3, ' ', STR_PAD_LEFT);
        $todoStr = str_pad("$todo", 3, ' ', STR_PAD_LEFT);

        $passedStr = CliStr::cl($passedColor, $passedStr);
        $exceptedStr = CliStr::cl($exceptedColor, $exceptedStr);
        $skippedStr = CliStr::cl($skippedColor, $skippedStr);
        $todoStr = CliStr::cl($todoColor, $todoStr);

        if ($hasOnly) {
            $passedStr .= ' | Tests are filtered by ' . CliStr::cl('cyan', '->only()');
        }

        // Log Text
        $lines = [""];
        $lines[] = CliStr::line('grey', true, '-');
        $lines[] = "  Tests:";
        $lines[] = CliStr::cl('grey', '   - All suite:') . $allTests;
        $lines[] = CliStr::cl('grey', '   - Passed:   ') . $passedStr;

        if ($excepted) {
            $lines[] = CliStr::cl('grey', '   - Excepted: ') . $exceptedStr;
        }

        if ($skipped) {
            $lines[] = CliStr::cl('grey', '   - Skipped:  ') . $skippedStr;
        }

        if ($todo) {
            $lines[] = CliStr::cl('grey', '   - Todo:     ') . $todoStr;
        }

        $lines[] = " Memory: " . $this->memorySize($maxMemory);
        $lines[] = "   Time: " . $this->getTime($this->logState->getTestingTime());
        $lines[] = "";
        $lines[] = CliStr::line('grey', true, '-');

        CliStr::write($lines);

        if ($hasExcepted) {
            exit(1);
        }
    }

    private function echoSuite(LogData $item): void
    {
        $line = ' ' . $this->getIcon($item) . ' '
            . $this->getMessage($item)
            . $this->memorySize($item->memoryUsage) . '  '
            . $this->getTime($item->timeUsage)
            . "\n";

        CliStr::write($line);
    }

    public function echoExpectedSuite(LogData $item): void
    {
        $msg = '';

        if (!is_null($item->exception)) {
            $msg = CliStr::cl('RL', ' ' . $item->exception->getMessage()) . "\n"
                . CliStr::cl('RL', '   ' . $item->exception->getFile())
                . CliStr::cl('grey', ':' . $item->exception->getLine())
                . "\n"
                . CliStr::cl('RL')
                . "\n";

            $trace = $item->exception->getTrace();

            if ($this->config['traceReverse']) {
                $trace = array_reverse($trace);
            }

            foreach ($trace as $t) {
                $msg .= $this->parseTraceItem($t);
            }
        }

        $line = ' ' . $this->getIcon($item) . ' '
        . $this->getMessage($item, true) . "\n"
        . $msg ?? $item->exception
        . "\n";

        CliStr::write($line);
    }

    private function parseTraceItem(array $v): string
    {
        $fileLine = CliStr::cl('b', $v['file'], false)
            . CliStr::cl('w', ':' . $v['line'], false);

        return CliStr::cl('R', "  " . $fileLine . "\t")
            . "\n"
            . $this->getContentByLine($v['file'], $v['line'])
            . "\n"
            . CliStr::line('grey', true)
            . CliStr::cl('c', $v['class'] . $v['type'] . $v['function'] . "(...)\n", false)
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
            $v = CliStr::cl('grey', str_pad("$start:", 3, ' ', STR_PAD_LEFT))
                . ' '
                . ($start === $line ? CliStr::cl('r', $v) : $v);
        }

        return implode("\n", $lines);
    }


    private function memorySize(int $size): string
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

    private function getMessage(LogData $item, bool $isError = false): string
    {
        $msg = str_pad($item->message, 50, ' ');

        if ($isError) {
            return CliStr::cl('red', $msg);
        }

        return $msg;
    }

    private function getIcon(LogData $item): string
    {
        return match (true) {
            $item->isSkip => CliStr::cl('grey', '-'),
            $item->isTodo => CliStr::cl('yellow', '!'),
            $item->isExcepted => CliStr::cl('red', '✘'),
            default => CliStr::cl('green', '✓'),
        };
    }

    private function getTime(float|int $time): string
    {
        return number_format($time, 6) . CliStr::cl('grey', ' s');
    }
}
