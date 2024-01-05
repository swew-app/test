<?php

declare(strict_types=1);

namespace Swew\Test\Suite;

use Closure;

final class SuiteGroup
{
    private ?Closure $beforeAll = null;

    private ?Closure $beforeEach = null;

    private ?Closure $afterEach = null;

    private ?Closure $afterAll = null;

    private array $suiteList = [];

    public static ?SuiteGroup $currentGroupInstance = null;

    public static ?Suite $currentSuite = null;

    public function __construct(
        public readonly string $testFilePath = ''
    ) {
        self::$currentGroupInstance = $this;

        require_once $testFilePath;

        self::$currentGroupInstance = null;
    }

    public static function addSuite(Suite $suite): void
    {
        if (is_null(self::$currentGroupInstance)) {
            throw new \LogicException('Empty $currentGroupInstance');
        }
        self::$currentGroupInstance->suiteList[] = $suite;
    }

    public static function addHook(SuiteHook $hook, Closure $hookFunction): void
    {
        $hookMethod = $hook->value;

        self::$currentGroupInstance->$hookMethod = $hookFunction;
    }

    public static function getCurrentSuite(): ?Suite
    {
        return  self::$currentSuite;
    }

    public function getCount(): int
    {
        return count($this->suiteList);
    }

    private function callHook(SuiteHook $hook): void
    {
        $hookMethod = $hook->value;
        $fn = $this->$hookMethod;

        if (!is_null($fn)) {
            $fn();
        }
    }

    public function runSuiteTests(
        array &$results,
        bool  $isFilteredByOnly,
        bool $isStopOnException,
        Closure $callback
    ): void {
        $list = $this->getSuites($isFilteredByOnly);

        $this->callHook(SuiteHook::BeforeAll);

        /** @var Suite $suite */
        foreach ($list as $suite) {
            self::$currentSuite = $suite;

            $this->callHook(SuiteHook::BeforeEach);

            $result = $suite->run(memory_get_usage());

            $results[] = $result;

            $this->callHook(SuiteHook::AfterEach);

            $callback($isStopOnException && $result->isExcepted);

            if ($isStopOnException && $result->isExcepted) {
                break;
            }
        }

        $this->callHook(SuiteHook::AfterAll);
    }

    public function hasOnly(): bool
    {
        /** @var Suite $suite */
        foreach ($this->suiteList as $suite) {
            if ($suite->isOnly) {
                return true;
            }
        }
        return false;
    }

    public function filterSuiteByMessage(string $messageFilter): void
    {
        $this->suiteList = array_filter(
            $this->suiteList,
            fn (Suite $suite) => str_contains($suite->message, $messageFilter)
        );
    }

    private function getSuites(bool $isFilteredByOnly): array
    {
        return $isFilteredByOnly
            ? array_filter($this->suiteList, fn ($s): bool => $s->isOnly)
            : $this->suiteList;
    }
}
