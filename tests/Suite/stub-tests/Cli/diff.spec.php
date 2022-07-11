<?php

declare(strict_types=1);


use SWEW\Test\Utils\CliStr;
use SWEW\Test\Utils\Diff;

//beforeAll(fn() => CliStr::withColor(false));


it('CLI: Diff::diff Str', function () {
    $str1 = "Hello woman!";
    $str2 = "Hi Man!";

    $res = Diff::diff($str1, $str2);
    $exp = <<<PHP_DATA
Hello woman!
Hi Man!
PHP_DATA;

    expect(trim($res))->toBe(trim($exp));
});

it('CLI: Diff::diff Arr=', function () {
    $str1 = ["h1"];
    $str2 = ["h1"];

    $res = Diff::diff($str1, $str2);
    $exp = '';

    expect($res)->toBe($exp);
});

it('CLI: Diff::diff Arr!=', function () {
    $str1 = ["h1", "h2", "h3"];
    $str2 = ["h3", "h2", "h1"];

    $res = Diff::diff($str1, $str2);

    dd($res);

    $exp = <<<PHP_DATA
array (
  0 => 'h1',
)
array (
  0 => 'h21',
)
PHP_DATA;

    expect(trim($res))->toBe(trim($exp));
})->only();


