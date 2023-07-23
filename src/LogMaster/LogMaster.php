<?php

declare(strict_types=1);

namespace Swew\Test\LogMaster;

use Swew\Test\Utils\CliArgs;
use Swew\Test\Utils\CliStr;
use Swew\Test\LogMaster\Log\LogData;
use Swew\Test\LogMaster\Log\LogState;
use Swew\Test\Utils\ConfigMaster;
use Swew\Test\Utils\DataConverter;

final class LogMaster
{
    public string $testFilePath = '';

    private readonly array $config;

    public function __construct(
        private readonly LogState $logState
    )
    {
        if (!empty($this->logState)) {
            $this->config = (array)ConfigMaster::getConfig('log');

            CliStr::vm()->withColor($this->config['color']);
            CliStr::vm()->setRootPath($this->logState->getRootPath());
        }
    }

    public function logListAndExit(): void
    {
        $list = $this->logState->getResults();

        $results = array_filter($list, fn($r) => $r->isExcepted === false);
        $results += array_filter($list, fn($r) => $r->isExcepted === true);

        $allTests = $this->logState->getTestsCount();
        $hasOnly = $this->logState->hasOnlyTests();
        $excepted = 0;
        $passed = 0;
        $skipped = 0;
        $todo = 0;
        $hasExcepted = false;
        $maxMemory = memory_get_peak_usage();

        CliStr::vm()->output->newLine();

        if ($this->config['short'] === false) {
            CliStr::vm()->output->writeLn(
                CliStr::vm()->getLine(), '<gray>%s</>'
            );
        }

        foreach ($results as $r) {
            if ($r->testFilePath !== $this->testFilePath) {
                $this->testFilePath = $r->testFilePath;
                $filePath = CliStr::vm()->trimPath($r->testFilePath);

                CliStr::vm()->output->writeLn($filePath, '<cyan>%s</>');
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

        $passedStr = "<$passedColor> $passedStr</>";
        $exceptedStr = "<$exceptedColor> $exceptedStr</>";
        $skippedStr = "<$skippedColor> $skippedStr</>";
        $todoStr = "<$todoColor> $todoStr</>";

        if ($hasOnly) {
            $passedStr .= ' | Tests are filtered by <cyan>->only()</>';
        }

        // Log Text
        $lines = [""];
        $lines[] = CliStr::vm()->getLine();

        // Filtering by file pattern
        $filePattern = CliArgs::getGlobMaskPattern('filter`');

        if (!is_null($filePattern)) {
            $lines[] = '<cyan>Filtered by file pattern (--file):</>';
            $lines[] = " \"<yellow>$filePattern</>\"";
            $lines[] = '';
        }


        // $filterSuiteByMsg
        if (!is_null($this->logState->getFilterSuiteByMsg())) {
            $lines[] = '<cyan>Filtered by suite pattern (--suite):</>';
            $lines[] = sprintf("<yellow>%s->getFilterSuiteByMsg() </>\"\n", $this->logState);
        }

        $lines[] = "  Tests:";
        $lines[] = "<gray>   - All suite:</> $allTests";
        $lines[] = "<gray>   - Passed:  </> $passedStr";

        if ($excepted) {
            $lines[] = "<gray>   - Excepted:</> $exceptedStr";
        }

        if ($skipped) {
            $lines[] = "<gray>   - Skipped:</> $skippedStr";
        }

        if ($todo) {
            $lines[] = "<gray>   - Todo:   </> $todoStr";
        }

        $lines[] = " Memory:  " . DataConverter::memorySize($maxMemory);
        $lines[] = "   Time:  " . DataConverter::getTime($this->logState->getTestingTime());
        $lines[] = "";
        $lines[] = CliStr::vm()->getLine();

        CliStr::vm()->write($lines);

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

        CliStr::vm()->write($line);
    }

    private function echoExceptedSuite(LogData $item): void
    {
        $msg = '';

        if (!is_null($item->exception)) {

            $fileLine = CliStr::vm()->trimPath($item->exception->getFile()) . ':' . $item->exception->getLine();

            $msg = $item->exception->getMessage() . "\n"
                . CliStr::vm()->getWithPrefix($fileLine, false)
                . "\n";

            $trace = $item->exception->getTrace();

            if ($this->config['traceReverse']) {
                $trace = array_reverse($trace);
            }

            foreach ($trace as $t) {
                if (
                    isset($t['file']) &&
                    (str_contains($t['file'], 'vendor/swew/test') || str_contains($t['file'], 'vendor/bin/t'))
                ) {
                    continue;
                }
                $msg .= DataConverter::parseTraceItem($t);
            }

            $msg .= $item->exception->getMessage();
        }

        $title = ' ' . DataConverter::getIcon($item) . ' ' . DataConverter::getMessage($item, true);

        $lines = [
            ($msg ?: $item->exception),
            $title,
        ];

        CliStr::vm()->write($lines);
    }
}
