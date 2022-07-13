<?php

declare(strict_types=1);

namespace SWEW\Test\LogMaster\Log;

final class LogState
{
    private array $results = [];

    private string $rootPath = '';

    private float $testingTime = 0;

    private int $testsCount = 0;

    private bool $hasOnlyTests = false;

    private ?string $filterSuiteByMsg = null;

    //

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results): void
    {
        $this->results = $results;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function setRootPath(string $rootPath): void
    {
        $this->rootPath = $rootPath;
    }

    public function getTestingTime(): float
    {
        return $this->testingTime;
    }

    public function setTestingTime(float $testingTime): void
    {
        $this->testingTime = $testingTime;
    }

    public function getTestsCount(): int
    {
        return $this->testsCount;
    }

    public function setTestsCount(int $testsCount): void
    {
        $this->testsCount = $testsCount;
    }

    public function hasOnlyTests(): bool
    {
        return $this->hasOnlyTests;
    }

    public function setHasOnlyTests(bool $hasOnlyTests): void
    {
        $this->hasOnlyTests = $hasOnlyTests;
    }

    public function getFilterSuiteByMsg(): ?string
    {
        return $this->filterSuiteByMsg;
    }

    public function setFilterSuiteByMsg(?string $filterSuiteByMsg): void
    {
        $this->filterSuiteByMsg = $filterSuiteByMsg;
    }
}
