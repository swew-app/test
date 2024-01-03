<?php

declare(strict_types=1);

expect()->extend('toBeOne', function (int $value) {
    // Custom error
    if ($value !== 1) {
        throw new Error('This is not 1');
    }

    // Expectation
    $this->toBe(1);
});

it('Extends expectation', function () {
    expect(1)->toBeOne();
});
