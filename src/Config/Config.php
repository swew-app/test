<?php

declare(strict_types=1);

namespace Swew\Test\Config;

use JsonSerializable;

class Config implements JsonSerializable
{
    public string $timezone = 'Europe/Moscow';

    public string $preloadFile = '';

    /** @var array<string> */
    public array $paths = ['*.spec.php', '*.test.php'];

    public bool $bail = false;

    public bool $logLogo = true;

    public bool $logColor = true;

    public bool $logTraceReverse = true;
    public bool $logClear = true;

    public bool $logShort = false;

    private string $root = '';

    private string $filter = '';

    private string $suite = '';

    /**
     * @var array<string>
     */
    private array $testFiles = [];

    private array $env = [];

    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'test' => [
                'timezone' => $this->timezone,
                'preloadFile' => $this->preloadFile,
                'paths' => $this->paths,
                'bail' => $this->bail,
                'log' => [
                    'logo' => $this->logLogo,
                    'color' => $this->logColor,
                    'traceReverse' => $this->logTraceReverse,
                    'clear' => $this->logClear,
                    'short' => $this->logShort
                ],
                'env' => [
                    '__TEST__' => true
                ]
            ]
        ];
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function setRoot(string $root): void
    {
        $path = realpath($root);
        if ($path === false || !is_dir($path)) {
            throw new \InvalidArgumentException("Invalid test directory: $root");
        }
        $this->root = $path;
    }

    public function getSuite(): string
    {
        return $this->suite;
    }

    public function setSuite(string $suite): void
    {
        $this->suite = $suite;
    }

    /** @return array<string> */
    public function getTestFiles(): array
    {
        return $this->testFiles;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    /** @param array<string> $testFiles */
    public function setTestFiles(array $testFiles): void
    {
        $this->testFiles = $testFiles;
    }
}
