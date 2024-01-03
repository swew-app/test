<?php

declare(strict_types=1);

it('Test 1', function () {
    //     $a = str_repeat("Hello", 1024 * 1024 * 4);
    // d($R, memory_get_usage());
    expect(10)->not()->toBe(1);
});

it('Test 2: with dataset', function (int $num, int $n2 = 3) {
    $a = str_repeat("Hello", $num * 100000);

    return $a;
})->with([
    1,
    [2, 3]
]);

it('Test 3: skip', function () {
    sleep(2);
})->skip();

it('Test 4: todo with empty functions');

it('DIFF: new', function () {
    expect("Привет дивный \n новый")
        ->toBe('Привет Мир дивный');
})->skip();
