<?php

declare(strict_types=1);

use SWEW\Test\Expectations\Expectation;
use SWEW\Test\Suite\Suite;
use SWEW\Test\Runner\TestManager;

/*
https://www.php.net/manual/ru/function.getrusage.php

----

https://www.php.net/manual/ru/function.memory-get-usage.php

echo memory_get_usage() . "\n"; // 36640
$a = str_repeat("Hello", 4242);
echo memory_get_usage() . "\n"; // 57960
unset($a);
echo memory_get_usage() . "\n"; // 36744

*/

$_exit = function (String $name): void {
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
