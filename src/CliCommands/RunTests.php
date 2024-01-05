<?php

declare(strict_types=1);

namespace Swew\Test\CliCommands;

use LogicException;
use Swew\Cli\Command;
use Swew\Test\LogMaster\Log\LogData;
use Swew\Test\Suite\SuiteGroup;
use Swew\Test\TestMaster;

class RunTests extends Command
{
    public const NAME = 'runTests';

    public const DESCRIPTION = 'Run tests';

    private array $suiteGroupList = [];

    private float $testingTime = 0;

    public function __invoke(): int
    {
        /** @var TestMaster $commander */
        $commander = $this->getCommander();

        if (!($commander instanceof TestMaster)) {
            throw new LogicException('Is not testMaster');
        }

        // Preload file
        $this->requirePreloadFile(
            $commander->config->getRoot(),
            $commander->config->preloadFile
        );

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

        return self::SUCCESS;
    }

    private function requirePreloadFile(string $root, string $preloadFile): void
    {
        if (!empty($preloadFile)) {
            $this->output?->writeLn($root . DIRECTORY_SEPARATOR . $preloadFile, '<green>Preload file</><br><cyan> %s</>');
            require realpath($root . DIRECTORY_SEPARATOR . $preloadFile);
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

    private function runTests(TestMaster $commander): array
    {
        if (!($this->output)) {
            throw new LogicException('Empty output');
        }

        $isStopOnException = $commander->config->bail;

        $testsCount = $this->getCount();

        $bar = $this->output->createProgressBar($testsCount);
        $bar->start(); // show progress bar

        $startTime = microtime(true);

        $isFilteredByOnly = $this->hasTestWithOnly();

        /** @var array<LogData> $results */
        $results = [];

        $isStoppedByException = false;

        // Run tests
        /** @var SuiteGroup $suiteGroup */
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

        /** @var SuiteGroup $group */
        foreach ($this->suiteGroupList as $group) {
            $count += $group->getCount();
        }

        return $count;
    }

    private function hasTestWithOnly(): bool
    {
        /** @var SuiteGroup $suiteGroup */
        foreach ($this->suiteGroupList as $suiteGroup) {
            $isFilteredByOnly = $suiteGroup->hasOnly();

            if ($isFilteredByOnly) {
                return true;
            }
        }

        return false;
    }
}
