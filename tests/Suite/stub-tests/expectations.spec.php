<?php

declare(strict_types=1);

it('toBe', function () {
    expect(10)->toBe(10);
    expect(10)->not()->toBe(1);
});

it('toBeArray', function () {
    expect([1])->toBeArray();
    expect(1)->not()->toBeArray();
});

it('toBeTrue', function () {
    expect(true)->toBeTrue();
    expect(false)->not()->toBeTrue();
});

it('toBeTruthy', function () {
    expect(1)->toBeTruthy();
    expect('true')->toBeTruthy();
    expect(true)->toBeTruthy();

    expect(0)->not()->toBeTruthy();
    expect('')->not()->toBeTruthy();
    expect(false)->not()->toBeTruthy();
});

it('toBeFalse', function () {
    expect(false)->toBeFalse();
    expect(true)->not()->toBeFalse();
});

it('toBeFalsy', function () {
    expect(0)->toBeFalsy();
    expect('')->toBeFalsy();
    expect(false)->toBeFalsy();

    expect(1)->not()->toBeFalsy();
    expect('true')->not()->toBeFalsy();
    expect(true)->not()->toBeFalsy();
});

it('toBeGreaterThan', function () {
    expect(0)->toBeGreaterThan(-1);
    expect(0)->not()->toBeGreaterThan(1);
});

it('toBeGreaterThanOrEqual', function () {
    expect(0)->toBeGreaterThanOrEqual(-1);
    expect(0)->toBeGreaterThanOrEqual(0);
    expect(0)->not()->toBeGreaterThanOrEqual(1);
    expect(1)->not()->toBeGreaterThanOrEqual(1);
});

it('toBeLessThan', function () {
    expect(0)->toBeLessThan(1);
    expect(0)->not()->toBeLessThan(-1);
});

it('toBeLessThanOrEqual', function () {
    expect(0)->toBeLessThanOrEqual(0);
    expect(0)->toBeLessThanOrEqual(1);
    expect(0)->not()->toBeLessThanOrEqual(0);
    expect(0)->not()->toBeLessThanOrEqual(-1);
});

it('toContain', function () {
    expect(0)->toContain(1);
    expect(0)->not()->toContain(0);
})->only();

it('toHaveCount', function () {
})->skip();
it('toHaveProperty', function () {
})->skip();
it('toHaveProperties', function () {
})->skip();
it('toMatchArray', function () {
})->skip();
it('toMatchObject', function () {
})->skip();
it('toEqual', function () {
})->skip();
it('toEqualCanonicalizing', function () {
})->skip();
it('toEqualWithDelta', function () {
})->skip();
it('toBeIn', function () {
})->skip();
it('toBeInfinite', function () {
})->skip();
it('toBeInstanceOf', function () {
})->skip();
it('toBeBool', function () {
})->skip();
it('toBeCallable', function () {
})->skip();
it('toBeFloat', function () {
})->skip();
it('toBeInt', function () {
})->skip();
it('toBeIterable', function () {
})->skip();
it('toBeNumeric', function () {
})->skip();
it('toBeObject', function () {
})->skip();
it('toBeResource', function () {
})->skip();
it('toBeScalar', function () {
})->skip();
it('toBeString', function () {
})->skip();
it('toBeJson', function () {
})->skip();
it('toBeNan', function () {
})->skip();
it('toBeNull', function () {
})->skip();
it('toHaveKey', function () {
})->skip();
it('toHaveKeys', function () {
})->skip();
it('toHaveLength', function () {
})->skip();
it('toBeReadableDirectory', function () {
})->skip();
it('toBeWritableDirectory', function () {
})->skip();
it('toStartWith', function () {
})->skip();
it('toThrow', function () {
})->skip();
it('toEndWith', function () {
})->skip();
it('toMatch', function () {
})->skip();
it('toMatchConstraint', function () {
})->skip();
it('dd', function () {
})->skip();
it('each', function () {
})->skip();
it('json', function () {
})->skip();
it('sequence', function () {
})->skip();
it('unless', function () {
})->skip();
it('when', function () {
})->skip();
