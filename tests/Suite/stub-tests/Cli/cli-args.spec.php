<?php

declare(strict_types=1);

use SWEW\Test\Utils\CliArgs;
use SWEW\Test\Utils\CliStr;

it('CLI: parse args', function () {
    $args = ['path/to/file.php', '-f', 'spec.php', '--no-color'];

    $options = [
        'file,f' => [
            'desc' => 'Filter files',
        ],
        'no-color' => [
            'desc' => 'Use color',
        ],
    ];

    CliArgs::init($args, $options);

    expect(CliArgs::val('file'))->toBe('spec.php');
    expect(CliArgs::val('f'))->toBe('spec.php');
    expect(CliArgs::val('no-color'))->toBe(true);
});


it('CLI: exception not found options', function () {
    $args = ['path/to/file.php'];

    $options = [];

    CliArgs::init($args, $options);

    expect(fn() => CliArgs::val('lorem'))->toThrow(Exception::class);
});

it('CLI: getHelp', function () {
    $args = ['path/to/file.php', '-h'];

    $options = [
        'file,f' => [
            'desc' => 'Filter files',
        ],
        'no-color' => [
            'desc' => 'Use color',
        ],
    ];

    CliArgs::init($args, $options);
    CliStr::withColor(false);

    expect(CliArgs::val('help'))->toBe(true);

    $expected = <<<PHP_DATA

Help information.

 -help, -h:     Show help
 -file, -f:     Filter files
 -no-color:     Use color

PHP_DATA;


    $a1 = explode("\n", CliArgs::getHelp());
    $a2 = explode("\n", $expected);

    dd(
         array_diff($a1, $a2),
         array_diff($a2, $a1)
    );

    expect(CliArgs::getHelp())->toBe($expected);
})->todo();

