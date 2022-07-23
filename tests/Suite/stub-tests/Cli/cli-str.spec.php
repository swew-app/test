<?php

declare(strict_types=1);

use Swew\Test\Utils\CliStr;

beforeAll(fn () => CliStr::withColor(true));
afterAll(fn () => CliStr::withColor(false));

it('CliStr: clearColor', function () {
    $str = $exp = 'Some text';

    $str = CliStr::cl('red', $str);
    $str = CliStr::cl('RL', $str);
    $str = CliStr::cl('Ug', $str);

    expect($str)->not->toBe($exp);

    $str = CliStr::clearColor($exp);

    expect($str)->toBe($exp);
});
