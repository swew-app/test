<?php

declare(strict_types=1);

use SWEW\Test\Expectations\Expectation;
use SWEW\Test\Suite\Suite;
use SWEW\Test\Suite\SuiteHook;
use SWEW\Test\TestRunner;

$_exit = function (string $name): void {
    fwrite(STDERR, "The global function `{$name}()`s can't be created because of some naming collisions with another library.\n");
};



if (!function_exists('__getFilePath')) {
    function __getFilePath(): string
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);

        return $backtrace[1]['file'];
    }
} else {
    $_exit('__getFilePath');
}

if (!function_exists('it')) {
    function it(string $message, Closure $closure): Suite
    {
        $suite = new Suite($message, $closure);

        $suite->testFilePath = __getFilePath();

        TestRunner::add($suite);

        return $suite;
    }
} else {
    $_exit('it');
}

if (!function_exists('xit')) {
    function xit(string $message, Closure $closure): Suite
    {
        $suite = new Suite($message, $closure);

        $suite->testFilePath = __getFilePath();

        $suite->skip();

        TestRunner::add($suite);

        return $suite;
    }
} else {
    $_exit('xit');
}

if (!function_exists('fit')) {
    function fit(string $message, Closure $closure): Suite
    {
        $suite = new Suite($message, $closure);

        $suite->testFilePath = __getFilePath();

        $suite->only();

        TestRunner::add($suite);

        return $suite;
    }
} else {
    $_exit('fit');
}

if (!function_exists('expect')) {
    function expect(mixed $value = null, string $message = ''): Expectation
    {
        return new Expectation($value, $message);
    }
} else {
    $_exit('expect');
}

if (!function_exists('beforeAll')) {
    function beforeAll(Closure $closure): void
    {
        TestRunner::addHook(SuiteHook::BeforeAll, $closure);
    }
} else {
    $_exit('beforeAll');
}

if (!function_exists('beforeEach')) {
    function beforeEach(Closure $closure): void
    {
        TestRunner::addHook(SuiteHook::BeforeEach, $closure);
    }
} else {
    $_exit('beforeEach');
}

if (!function_exists('afterEach')) {
    function afterEach(Closure $closure): void
    {
        TestRunner::addHook(SuiteHook::AfterEach, $closure);
    }
} else {
    $_exit('afterEach');
}

if (!function_exists('afterAll')) {
    function afterAll(Closure $closure): void
    {
        TestRunner::addHook(SuiteHook::AfterAll, $closure);
    }
} else {
    $_exit('afterAll');
}
