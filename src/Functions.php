<?php

declare(strict_types=1);

use SWEW\Test\Expectations\Expectation;
use SWEW\Test\Suite\Suite;
use SWEW\Test\Runner\TestManager;
use SWEW\Test\Suite\SuiteHook;

$_exit = function (string $name): void {
    fwrite(STDERR, "The global function `{$name}()`s can't be created because of some naming collisions with another library.\n");
};

if (!function_exists('it')) {
    function it(string $message, Closure $closure): Suite
    {
        $suite = new Suite($message, $closure);

        TestManager::add($suite);

        return $suite;
    }
} else {
    $_exit('it');
}

if (!function_exists('expect')) {
    function expect(mixed $value): Expectation
    {
        return new Expectation($value);
    }
} else {
    $_exit('expect');
}

if (!function_exists('beforeAll')) {
    function beforeAll(Closure $closure): void
    {
        TestManager::addHook(SuiteHook::BeforeAll, $closure);
    }
} else {
    $_exit('beforeAll');
}

if (!function_exists('beforeEach')) {
    function beforeEach(Closure $closure): void
    {
        TestManager::addHook(SuiteHook::BeforeEach, $closure);
    }
} else {
    $_exit('beforeEach');
}

if (!function_exists('afterEach')) {
    function afterEach(Closure $closure): void
    {
        TestManager::addHook(SuiteHook::AfterEach, $closure);
    }
} else {
    $_exit('afterEach');
}

if (!function_exists('afterAll')) {
    function afterAll(Closure $closure): void
    {
        TestManager::addHook(SuiteHook::AfterAll, $closure);
    }
} else {
    $_exit('afterAll');
}
