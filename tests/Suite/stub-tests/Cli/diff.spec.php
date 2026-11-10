<?php

declare(strict_types=1);

use Swew\Test\Utils\CliStr;
use Swew\Test\Utils\DataConverter;
use Swew\Test\Utils\Diff;

it('CLI: highlighter preserves its output', function (string $input, string $output) {
    expect(DataConverter::highlightSting($input))->toBe($output);
})->with([
    ['  A  B  ',      'A B'],
    ["Array\n(\n)\n", '<red>[<green> <red>]</>'],
    [' a => b ',      'a <gray>=></> b']
]);

it('CLI: highlighter falls back to plain text when PCRE fails', function () {
    $input = "Array\n(\n    [name] => value\n)\n";
    $limit = ini_get('pcre.backtrack_limit');
    try {
        ini_set('pcre.backtrack_limit', '1');
        $result = DataConverter::highlightSting($input);
    } finally {
        ini_set('pcre.backtrack_limit', $limit);
    }
    expect($result)->toBe(trim($input));
});

it('CLI: unavailable source does not hide the trace', function () {
    $file = sys_get_temp_dir() . '/swew-missing-' . bin2hex(random_bytes(8));
    $trace = DataConverter::parseTraceItem(['file' => $file, 'line' => 42]);
    expect($trace)->toContain(basename($file) . ':42');
    expect($trace)->toContain('Source unavailable');
    expect(DataConverter::getExceptionTraceLine(null))->toBe('');
    expect(DataConverter::getExceptionTraceLine(['file' => 'source.php']))->toBe('source.php');
    expect(DataConverter::parseTraceItem(['function' => 'strlen', 'line' => 1]))->toContain('strlen(...)');
});

it('CLI: time formatting preserves units and fractions', function () {
    expect(DataConverter::getTime(1.25))->toBe('1.25<gray>s</>');
    expect(DataConverter::getTime(0.05))->toBe('50.00<gray>ms</>');
    expect(DataConverter::getTime(0.000012))->toBe('12.00<gray>µs</>');
    expect(DataConverter::formatMicrotime(62.125))->toBe('01:02.125');
    expect(DataConverter::formatMicrotime(-62.125))->toBe('01:02.125');
});

it('CLI: Diff::diff Str!=', function () {
    $str1 = 'Hello woman!';
    $str2 = 'Hi Man!';

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
})->with([
    ['second', 'compare:'],
    ['',       'value:']
]);

it('CLI: Diff::diff Arr==', function () {
    $str1 = ['h1'];
    $str2 = ['h1'];

    $res = Diff::diff($str1, $str2);
    $res = CliStr::vm()->output->clearColor($res);
    $exp = '';

    expect($res)->toBe($exp);
});

it('CLI: Diff::diff Arr!=', function () {
    $str1 = ['h1', 'h2', 'h3', 'h4', 'h5'];
    $str2 = ['h3', 'h2', 'h1', 'h4', 'h5'];

    $res = Diff::diff($str1, $str2, false);
    $res = CliStr::vm()->output->clearColor($res);
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
