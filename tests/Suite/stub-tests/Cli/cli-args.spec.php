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

    expect(fn () => CliArgs::val('lorem'))->toThrow(Exception::class);
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

    expect(CliArgs::val('help'))->toBe(true);

    $exp = "Help information.\n\n" .
        " -help, -h:\tShow help\n" .
        " -file, -f:\tFilter files\n" .
        " -no-color:\tUse color\n";

    $res = CliStr::clearColor(CliArgs::getHelp());
    $exp = CliStr::clearColor($exp);

    expect(trim($res))->toBe(trim($exp));
});


it('CLI: hasArgs', function (bool $expected, array $props) {
    $args = array_merge(['path/to/file.php'], $props);

    $options = [
        'file,f' => [
            'desc' => 'Filter files',
        ],
        'no-color' => [
            'desc' => 'Use color',
        ],
    ];

    CliArgs::init($args, $options);

    $res = CliArgs::hasArgs();

    expect($res)->toBe($expected);
})
    ->with([
        [false, []],
        [false, ['test-file-name.spec.php']],
        [true, ['-f', 'file.spec.php']],
    ]);


it('CLI: hasArgs,hasCommand,getCommands', function () {
    $args = ['path/to/file.php', 'file.spec.php'];

    $options = [
        'file,f' => [
            'desc' => 'Filter files',
        ],
        'no-color' => [
            'desc' => 'Use color',
        ],
    ];

    CliArgs::init($args, $options);

    expect(CliArgs::hasArgs())->toBe(false);

    expect(CliArgs::hasCommand())->toBe(true);

    expect(CliArgs::getCommands())->toBe(['file.spec.php']);
});


it('CLI: getFilePattern 1', function () {
    $args = ['path/to/file.php', 'file.spec.php'];

    $options = [
        'file,f' => [
            'desc' => 'Filter files',
        ],
    ];

    CliArgs::init($args, $options);

    expect(CliArgs::getFilePattern())->toBe('**/*file.spec.php*');
});

it('CLI: getFilePattern 2', function () {
    $args = ['path/to/file.php', '-f', 'file-1.spec.php'];

    $options = [
        'file,f' => [
            'desc' => 'Filter files',
        ],
    ];

    CliArgs::init($args, $options);

    expect(CliArgs::getFilePattern('file'))->toBe('**/*file-1.spec.php*');
});

it('CLI: getFilePattern 3', function () {
    $args = ['path/to/file.php'];

    $options = [
        'file,f' => [
            'desc' => 'Filter files',
        ],
    ];

    CliArgs::init($args, $options);

    expect(CliArgs::getFilePattern('file'))->toBe(null);
});
