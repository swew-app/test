<?php

declare(strict_types=1);

namespace Swew\Test\CliCommands;

use LogicException;
use Swew\Cli\Command;
use Swew\Test\LogMaster\Log\LogData;
use Swew\Test\TestMaster;
use Swew\Test\Utils\CliStr;
use Swew\Test\Utils\DataConverter;

class ShowTestResults extends Command
{
    public const NAME = 'log-tests';

    public const DESCRIPTION = 'Log test results';

    private bool $hasException = false;

    private bool $isShort = false;

    private int $allTests = 0;
    private int $allTestFilesCount = 0;
    private int $passedTests = 0;
    private int $exceptedTests = 0;
    private int $skippedTests = 0;
    private int $todoTests = 0;
    private int $finishAt = 0;

    public function __invoke(): int
    {
        /** @var TestMaster $commander */
        $commander = $this->getCommander();

        if (!($commander instanceof TestMaster)) {
            throw new LogicException('Is not testMaster');
        }

        if (!($this->output)) {
            throw new LogicException('Empty output');
        }

        $this->finishAt = time();

        // clear
        if ($commander->config->logClear) {
            $this->output->clear();
        }

        // logo
        if ($commander->config->logLogo) {
            $this->showLogo();
        }

        // TODO: короткое отображение
        if ($commander->config->logShort) {
            $this->isShort = true;
        }

        // лог файлов
        $this->showFiles($commander->testResults);

        // лог ошибок показываем в конце если они произошли
        if ($this->hasException) {
            $this->showExceptions(
                $commander->testResults,
                $commander->config->logTraceReverse
            );
        }

        // отображение summary
        $this->showSummary($commander);

        return self::SUCCESS;
    }

    private function showLogo(): void
    {
        $logo = [
            '<green>',
            ' __   _       ____  _      ',
            '( (` \ \    /| |_  \ \    /',
            '_)_)  \_\/\/ |_|__  \_\/\/ ',
            '      .-. .-. .-. .-.      ',
            '       |  |-  `-.  |       ',
            '     \'  `-\' `-\'  \'     ',
            'php: ' . PHP_VERSION . ';',
            '</>',
        ];

        $width = $this->output?->width() ?? 80;

        foreach ($logo as &$v) {
            $v = str_pad($v, $width, ' ', STR_PAD_BOTH);
        }

        $this->output?->writeLn(implode("\n", $logo));
    }

    private function showFiles(array $logDataList): void
    {
        if (!($this->output)) {
            throw new LogicException('Empty output');
        }

        $this->allTests = count($logDataList);

        $filePath = '';

        /** @var LogData $item */
        foreach ($logDataList as $item) {
            if ($item->isExcepted) {
                $this->hasException = true;
            }

            if ($filePath !== $item->testFilePath) {
                $this->allTestFilesCount++;

                $filePath = $item->testFilePath;

                $this->output->writeLn(CliStr::vm()->trimPath($filePath), '<cyan>%s</>');
            }

            if (!$this->isShort) {
                $line = DataConverter::getTestSuiteLine($item);
                $this->output->writeLn($line);
            }

            $this->setCount($item);
        }
    }

    private function setCount(LogData $item): void
    {
        switch (true) {
            case $item->isExcepted:
                $this->exceptedTests++;
                break;

            case $item->isSkip:
                $this->skippedTests++;
                break;

            case $item->isTodo:
                $this->todoTests++;
                break;

            default:
                $this->passedTests++;
        }
    }

    private function showExceptions(array $logDataList, bool $traceReverse): void
    {
        if (!($this->output)) {
            throw new LogicException('Empty output');
        }

        $exceptNumber = 0;

        /** @var LogData $item */
        foreach ($logDataList as $item) {
            if (!$item->isExcepted || is_null($item->exception)) {
                continue;
            }

            ++$exceptNumber;
            $suitTitle = DataConverter::getMessage($item);

            $this->output->writeLn(
                CliStr::vm()->getLine("[ $exceptNumber | $suitTitle ]", '<red>')
            );

            $trace = $item->exception->getTrace();

            if ($traceReverse) {
                $trace = array_reverse($trace);
            }

            $msg = '';

            foreach ($trace as $t) {
                if (
                    isset($t['file']) &&
                    (str_contains($t['file'], 'vendor/swew/test') || str_contains($t['file'], 'vendor/bin/t'))
                ) {
                    continue;
                }
                $msg .= DataConverter::parseTraceItem($t);
            }

            if (empty($msg)) {
                $msg = strval($item->exception);
            }

            $this->output->writeLn($msg);


            $filePath = DataConverter::getExceptionTraceLine($item->exception->getTrace()[0]);
            $this->output->writeLn($filePath, '<cyan>%s</>');

            $this->output->writeLn(DataConverter::getIcon($item) . ' <red>' . $suitTitle . '</>');

            $this->output->writeLn($item->exception->getMessage());
        }
    }

    private function showSummary(TestMaster $commander): void
    {
        $filePattern = $commander->config->getFilter();
        $suiteFilter = $commander->config->getSuite();
        $startAt = $commander->startAt;

        if (!($this->output)) {
            throw new LogicException('Empty output');
        }

        if (!empty($filePattern)) {
            $this->output->writeLn("<cyan>Filtered by file pattern (--filter): <yellow>$filePattern</>");
        }

        if (!empty($suiteFilter)) {
            $this->output->writeLn("<cyan>Filtered by suite pattern (--suite):<yellow> {$suiteFilter}</>");
        }

        $tests = [];

        if ($this->exceptedTests > 0) {
            $tests[] = "<red>{$this->exceptedTests} excepted</>";
        }

        $tests[] = "<green>{$this->passedTests} passed</>";

        if ($this->skippedTests > 0) {
            $tests[] = "<gray>{$this->skippedTests} skipped</>";
        }

        if ($this->todoTests > 0) {
            $tests[] = "<yellow>{$this->todoTests} todo</>";
        }

        $tst = implode(' | ', $tests);

        $timeSrt = date('Y.m.d H:i:s', intval($startAt));
        $duration = DataConverter::formatMicrotime($startAt - $this->finishAt);
        $memory = DataConverter::memorySize(memory_get_peak_usage());


        $lines = [
            CliStr::vm()->getLine(),
            " Test Files  <cyan>{$this->allTestFilesCount}</>",
            "      Tests  $tst <green>({$this->allTests})</>",
            "",
            " Max memory  <yellow>{$memory}</>",
            "   Start at  <gray>{$timeSrt}</>",
            "   Duration  <cyan>{$duration}</><gray> s</>",
            ""
        ];

        $this->output->writeLn(implode("\n", $lines));
    }
}
