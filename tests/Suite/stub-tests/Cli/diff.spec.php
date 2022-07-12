<?php

declare(strict_types=1);


use SWEW\Test\Utils\CliStr;
use SWEW\Test\Utils\Diff;

beforeAll(fn () => CliStr::withColor(false));
afterAll(fn () => CliStr::withColor(true));


it('CLI: Diff::diff Str==', function () {
    $str1 = "Hello woman!";
    $str2 = "Hi Man!";

    $res = Diff::diff($str1, $str2, false);
    $exp = <<<PHP_DATA
 Hello woman!
 Hi Man!
PHP_DATA;

    expect(trim($res))->toBe(trim($exp));
})->only();

it('CLI: Diff::diff Arr==', function () {
    $str1 = ["h1"];
    $str2 = ["h1"];

    $res = Diff::diff($str1, $str2);
    $exp = '';

    expect($res)->toBe($exp);
})->only();

it('CLI: Diff::diff Arr!=', function () {
    $str1 = ["h1", "h2", "h3", "h4", "h5"];
    $str2 = ["h3", "h2", "h1", "h4", "h5"];

    $res = Diff::diff($str1, $str2, false);

    $exp = <<<PHP_DATA
 array (
   0 => 'h1',
   1 => 'h2',
   2 => 'h3',
   3 => 'h4',
   4 => 'h5',
 )
 array (
   0 => 'h3',
   1 => 'h2',
   2 => 'h1',
   3 => 'h4',
   4 => 'h5',
 )
PHP_DATA;

    expect(trim($res))->toBe(trim($exp));
})->only();
