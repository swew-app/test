<?php

declare(strict_types=1);

namespace SWEW\Test\LogMaster\Log;

final class LogState
{
    private array $config = [];

    private array $results = [];

    private string $rootDir = '';

    private float $testingTime = 0;

    private int $testsCount = 0;

    private bool $hasOnlyTests = false;

    //

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results): void
    {
        $this->results = $results;
    }

    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function setRootDir(string $rootDir): void
    {
        $this->rootDir = $rootDir;
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
}
