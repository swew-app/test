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
            $this->commander->config['_root'],
            $this->commander->config['preloadFile']
        );

        // Filter Suite
        $suiteFilter = $this->commander->config['_suite'];

        // Tests
        $files = $this->commander->config['_testFiles'];

        foreach ($files as $file) {
            $this->loadTestFile($file, $suiteFilter);
        }

        // Run Tests
        $commander->testResults = $this->runTests($suiteFilter);

        $commander->testingTime = $this->testingTime;

        return self::SUCCESS;
    }

    private function requirePreloadFile(string $root, string $preloadFile): void
    {
        if (!empty($preloadFile)) {
            require realpath($root . $preloadFile);
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

    private function runTests(string $suiteFilter): array
    {
        if (!($this->output)) {
            throw new LogicException('Empty output');
        }

        $testsCount = $this->getCount();

        $bar = $this->output->createProgressBar($testsCount);
        $bar->start(); // show progress bar

        $startTime = microtime(true);

        $isFilteredByOnly = $this->hasTestWithOnly();

        /** @var array<LogData> $results */
        $results = [];

        // Run tests
        /** @var SuiteGroup $suiteGroup */
        foreach ($this->suiteGroupList as $suiteGroup) {
            // TODO: stop on bail
            $suiteGroup->runSuiteTests(
                $results,
                $isFilteredByOnly,
                function () use ($bar) {
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
