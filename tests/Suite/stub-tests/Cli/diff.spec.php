<?php

declare(strict_types=1);

use Swew\Test\Utils\CliStr;
use Swew\Test\Utils\Diff;

it('CLI: Diff::diff Str!=', function () {
    $str1 = "Hello woman!";
    $str2 = "Hi Man!";

    $res = Diff::diff($str1, $str2, false);
    $res = CliStr::vm()->output->clearColor($res);
    $exp = <<<PHP_DATA
❯Hello woman!
❯Hi Man!
PHP_DATA;

    expect(trim($res))->toBe(trim($exp));
});

it('CLI: Diff titles can be hidden', function (string $second, string $title) {
    expect(Diff::diff('first', $second))->toContain($title);
    expect(Diff::diff('first', $second, false))->not()->toContain($title);
})->with([['second', 'compare:'], ['', 'value:']]);

it('CLI: Diff::diff Arr==', function () {
    $str1 = ["h1"];
    $str2 = ["h1"];

    $res = Diff::diff($str1, $str2);
    $res =  CliStr::vm()->output->clearColor($res);
    $exp = '';

    expect($res)->toBe($exp);
});

it('CLI: Diff::diff Arr!=', function () {
    $str1 = ["h1", "h2", "h3", "h4", "h5"];
    $str2 = ["h3", "h2", "h1", "h4", "h5"];

    $res = Diff::diff($str1, $str2, false);
    $res =  CliStr::vm()->output->clearColor($res);
    $exp = <<<PHP_DATA
array (
❯  0 => 'h1',
❯  0 => 'h3',
  1 => 'h2',
❯  2 => 'h3',
❯  2 => 'h1',
  3 => 'h4',
  4 => 'h5',
)
PHP_DATA;

    expect(trim($res))->toBe(trim($exp));
});
