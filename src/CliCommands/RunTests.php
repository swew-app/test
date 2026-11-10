<?php

declare(strict_types=1);

namespace Swew\Test\CliCommands;

use LogicException;
use Swew\Cli\Args\CliCommand;
use Swew\Cli\Args\CliCtx;
use Swew\Cli\CommandCtx;
use Swew\Test\LogMaster\Log\LogData;
use Swew\Test\Suite\SuiteGroup;
use Swew\Test\TestMaster;

#[CliCommand(name: 'runTests', desc: 'Run tests')]
class RunTests
{
    #[CliCtx]
    private CommandCtx $ctx;

    /** @var list<SuiteGroup> */
    private array $suiteGroupList = [];

    private float $testingTime = 0;

    public function __invoke(): int
    {
        $commander = $this->ctx->getCommander();

        if (!$commander instanceof TestMaster) {
            throw new LogicException('Is not testMaster');
        }

        if (!empty($commander->config->timezone)) {
            \date_default_timezone_set($commander->config->timezone);
        }

        // Preload file
        $this->requirePreloadFile($commander->config->getRoot(), $commander->config->preloadFile);

        // Filter Suite
        $suiteFilter = $commander->config->getSuite();

        // Tests
        $files = $commander->config->getTestFiles();

        foreach ($files as $file) {
            $this->loadTestFile($file, $suiteFilter);
        }

        // Run Tests
        $commander->testResults = $this->runTests($commander);

        $commander->testingTime = $this->testingTime;

        return 0;
    }

    private function requirePreloadFile(string $root, string $preloadFile): void
    {
        if (!empty($preloadFile)) {
            $this->ctx->output->writeLn(
                $root . DIRECTORY_SEPARATOR . $preloadFile,
                '<green>Preload file</><br><cyan> %s</>'
            );
            require $root . DIRECTORY_SEPARATOR . $preloadFile;
        }
    }

    private function loadTestFile(string $file, string $suiteFilter): void
    {
        $group = new SuiteGroup($file);

        if (!empty($suiteFilter)) {
            $group->filterSuiteByMessage($suiteFilter);
        }

        $this->suiteGroupList[] = $group;
    }

    /** @return array<LogData> */
    private function runTests(TestMaster $commander): array
    {
        $output = $this->ctx->output;

        $isStopOnException = $commander->config->bail;

        $testsCount = $this->getCount();

        $bar = $output->createProgressBar($testsCount);
        $bar->start(); // show progress bar

        $startTime = microtime(true);

        $isFilteredByOnly = $this->hasTestWithOnly();

        /** @var array<LogData> $results */
        $results = [];

        $isStoppedByException = false;

        // Run tests
        foreach ($this->suiteGroupList as $suiteGroup) {
            if ($isStoppedByException) {
                break;
            }

            // stop on bail
            $suiteGroup->runSuiteTests(
                $results,
                $isFilteredByOnly,
                $isStopOnException,
                function (bool $isStopByException) use ($bar, &$isStoppedByException) {
                    if ($isStopByException) {
                        $isStoppedByException = true;
                    }

                    $bar->increment(); // progress
                }
            );
        }

        $bar->finish(); // remove progressbar

        $this->testingTime = microtime(true) - $startTime;

        return $results;
    }

    private function getCount(): int
    {
        $count = 0;

        foreach ($this->suiteGroupList as $group) {
            $count += $group->getCount();
        }

        return $count;
    }

    private function hasTestWithOnly(): bool
    {
        foreach ($this->suiteGroupList as $suiteGroup) {
            $isFilteredByOnly = $suiteGroup->hasOnly();

            if ($isFilteredByOnly) {
                return true;
            }
        }

        return false;
    }
}
