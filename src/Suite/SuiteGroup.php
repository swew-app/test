<?php

declare(strict_types=1);

namespace SWEW\Test\Suite;

use Closure;

final class SuiteGroup
{
    private ?Closure $beforeAll = null;

    private ?Closure $beforeEach = null;

    private ?Closure $afterEach = null;

    private ?Closure $afterAll = null;

    private array $suiteList = [];

    public function __construct(
        public readonly string $testFilePath = ''
    ) {
    }

    public function addSuite(Suite $suite): void
    {
        $this->suiteList[] = $suite;
    }

    public function addHook(SuiteHook $hook, Closure $hookFunction): void
    {
        $hookMethod = $hook->value;

        $this->$hookMethod = $hookFunction;
    }

    private function callHook(SuiteHook $hook): void
    {
        $hookMethod = $hook->value;
        $fn = $this->$hookMethod;

        if (!is_null($fn)) {
            $fn();
        }
    }

    public function run(array &$results, bool &$hasOnlyFilteredTests, Closure $setCurrentSuite): void
    {
        if ($hasOnlyFilteredTests === false) {
            foreach ($this->suiteList as $suite) {
                if ($suite->isOnly) {
                    $hasOnlyFilteredTests = true;
                    break;
                }
            }
        }

        $list = $hasOnlyFilteredTests ? array_filter($this->suiteList, fn ($s): bool => $s->isOnly) : $this->suiteList;

        $this->callHook(SuiteHook::BeforeAll);

        foreach ($list as $suite) {
            $setCurrentSuite($suite);

            $this->callHook(SuiteHook::BeforeEach);

            $results[] = $suite->run(memory_get_usage());

            $this->callHook(SuiteHook::AfterEach);

            $setCurrentSuite(null);
        }

        $this->callHook(SuiteHook::AfterAll);
    }
}
