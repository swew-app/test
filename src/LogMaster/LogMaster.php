<?php

declare(strict_types=1);

namespace SWEW\Test\LogMaster;

use SWEW\Test\Utils\CliArgs;
use SWEW\Test\Utils\CliStr;
use SWEW\Test\LogMaster\Log\LogData;
use SWEW\Test\LogMaster\Log\LogState;
use SWEW\Test\Utils\ConfigMaster;
use SWEW\Test\Utils\DataConverter;

final class LogMaster
{
    public string $testFilePath = '';

    private readonly array $config;

    public function __construct(
        private readonly LogState $logState
    ) {
        if (!empty($this->logState)) {
            $this->config = (array)ConfigMaster::getConfig('log');

            CliStr::withColor($this->config['color']);
            CliStr::setRootPath($this->logState->getRootPath());
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
        $maxMemory = memory_get_peak_usage();

        CliStr::write("\n");

        if ($this->config['short'] === false) {
            CliStr::write(
                [
                    CliStr::line('grey', true, '-')
                ]
            );
        }

        foreach ($results as $r) {
            if ($r->testFilePath !== $this->testFilePath) {
                $this->testFilePath = $r->testFilePath;
                $filePath = CliStr::trimPath($r->testFilePath) . "\n";

                CliStr::write(CliStr::cl('cyan', $filePath));
            }

            if ($r->isExcepted) {
                ++$excepted;
                $hasExcepted = true;

                $this->echoExceptedSuite($r);
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

        // Filtering by file pattern
        $filePattern = CliArgs::getGlobMaskPattern('file');

        if (!is_null($filePattern)) {
            $lines[] = CliStr::cl('cyan', 'Filtered by file pattern (--file):');
            $lines[] = ' "' . CliStr::cl('yellow', $filePattern) . '"';
            $lines[] = '';
        }


        // $filterSuiteByMsg
        if (!is_null($this->logState->getFilterSuiteByMsg())) {
            $lines[] = CliStr::cl('cyan', 'Filtered by suite pattern (--suite):');
            $lines[] = ' "' . CliStr::cl('yellow', $this->logState->getFilterSuiteByMsg()) . '"';
            $lines[] = '';
        }

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

        $lines[] = " Memory: " . DataConverter::memorySize($maxMemory);
        $lines[] = "   Time: " . DataConverter::getTime($this->logState->getTestingTime());
        $lines[] = "";
        $lines[] = CliStr::line('grey', true, '-');

        CliStr::write($lines);

        if ($hasExcepted) {
            exit(1);
        }
    }

    private function echoSuite(LogData $item): void
    {
        if ($this->config['short']) {
            return;
        }

        $line = ' '
            . DataConverter::getIcon($item) . ' '
            . DataConverter::getMessage($item)
            . DataConverter::memorySize($item->memoryUsage) . ' '
            . DataConverter::getTime($item->timeUsage)
            . "\n";

        CliStr::write($line);
    }

    private function echoExceptedSuite(LogData $item): void
    {
        $msg = '';

        if (!is_null($item->exception)) {
            $msg = CliStr::cl('RL', ' ' . $item->exception->getMessage()) . "\n"
                . CliStr::cl('RL', '   ' . CliStr::trimPath($item->exception->getFile()))
                . CliStr::cl('grey', ':' . $item->exception->getLine())
                . "\n"
                . CliStr::cl('RL')
                . "\n";

            $trace = $item->exception->getTrace();

            if ($this->config['traceReverse']) {
                $trace = array_reverse($trace);
            }

            foreach ($trace as $t) {
                $msg .= DataConverter::parseTraceItem($t);
            }

            $msg .= ' ' . $item->exception->getMessage();
        }

        $title = ' ' . DataConverter::getIcon($item) . ' ' . DataConverter::getMessage($item, true);

        $lines = [
            ($msg ?: $item->exception),
            $title,
        ];

        CliStr::write($lines);
    }
}
