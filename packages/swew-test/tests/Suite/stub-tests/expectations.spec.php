<?php

declare(strict_types=1);

it('toBe', function () {
    expect(10)->toBe(10);
    expect(10)->not->toBe(1);
});

it('toBeArray', function () {
    expect([1])->toBeArray();
    expect(1)->not()->toBeArray();
});

it('toBeTrue', function () {
    expect(true)->toBeTrue();
    expect(false)->not()->toBeTrue();
});

it('toBeEmpty', function () {
    expect('')->toBeEmpty();
    expect(true)->not()->toBeEmpty();
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
    expect(0)->toContain(0);
    expect('102')->toContain('0');
    expect(0)->not()->toContain(1);
});

it('toHaveCount', function () {
    expect([0, 1, 2])->toHaveCount(3);
    expect([0, 1, 2])->not()->toHaveCount(1);
});

it('toHaveProperty', function () {
    class MyClass
    {
        public $test;
    }

    $item = new MyClass();

    expect($item)->toHaveProperty('test');
    expect($item)->not()->toHaveProperty('test-x');
});

it('toMatchArray', function () {
    $arr1 = [1, 2];
    $arr2 = [1, 2];

    expect($arr1)->toMatchArray($arr2);
});

it('toMatchObject', function () {
    class MyClass1
    {
        public $test;
    }

    class MyClass2
    {
        public $test;
    }

    $item1 = new MyClass2();
    $item2 = new MyClass2();

    expect($item1)->toMatchObject($item2);
    expect($item1)->not()->toMatchObject(new MyClass1());
});

it('toEqual', function () {
    expect(1)->toEqual(1);
    expect(1)->not()->toEqual(12);
});

it('toEqualWithDelta', function () {
    expect(14)->toEqualWithDelta(10, 5);
});

it('toBeIn', function () {
    expect(200)->toBeIn([200, 301, 302]);
    expect(201)->not()->toBeIn([200, 301, 302]);
});

it('toBeInstanceOf', function () {
    class MyClass3
    {
        public $test;
    }

    expect(new MyClass3())->toBeInstanceOf(MyClass3::class);
});

it('toBeBool', function () {
    expect(true)->toBeBool();
});

it('toBeCallable', function () {
    $controller = fn () => '1';

    expect($controller)->toBeCallable();
});

it('toBeFloat', function () {
    expect(10.2)->toBeFloat();
});

it('toBeInt', function () {
    expect(101)->toBeInt();
});

it('toBeIterable', function () {
    expect([1, 2, 3])->toBeIterable();
});

it('toBeNumeric', function () {
    expect('10')->toBeNumeric();
});

it('toBeObject', function () {
    $object = new stdClass();

    expect($object)->toBeObject();
});

it('toBeResource', function () {
    $handle = fopen('php://memory', 'r+');
    expect($handle)->toBeResource();
});

it('toBeScalar', function () {
    expect('1')->toBeScalar();
    expect(1)->toBeScalar();
    expect(1.0)->toBeScalar();
    expect(true)->toBeScalar();
    expect([1, '1'])->not()->toBeScalar();
});

it('toBeString', function () {
    expect('$string')->toBeString();
});

it('toBeJson', function () {
    expect('{"hello":"world"}')->toBeJson();
});

it('toBeNull', function () {
    expect(null)->toBeNull();
    expect('null')->not()->toBeNull();
});

it('toHaveKey', function () {
    expect(['name' => 'Nuno', 'surname' => 'Maduro'])->toHaveKey('name');
});

it('toHaveKeys', function () {
    expect(['id' => 1, 'name' => 'Nuno'])->toHaveKeys(['id', 'name']);
});

it('toHaveLength', function () {
    expect('Pest')->toHaveLength(4);
    expect(['Nuno', 'Maduro'])->toHaveLength(2);
});

it('toBeReadableDirectory', function () {
    expect('/tmp')->toBeReadableDirectory();
});

it('toBeWritableDirectory', function () {
    expect('/tmp')->toBeWritableDirectory();
});

it('toStartWith', function () {
    expect('12345')->toStartWith('123');
    expect('12345')->not()->toStartWith('0123');
});

it('toThrow', function () {
    expect(fn () => throw new Exception('Something happened.'))->toThrow(Exception::class);

    expect(fn () => throw new Exception('Something happened.'))->toThrow('Something happened.');

    expect(fn () => throw new Exception('Something happened.'))->toThrow(Exception::class, 'Something happened.');

    expect(fn ($x, $y) => $x + $y)->not()->toThrow(Exception::class);
});

it('toEndWith', function () {
    expect('Hello World')->toEndWith('World');

    expect('Hello World')->not()->toEndWith('hi');
});

it('toMatch', function () {
    expect('Hello World')->toMatch('/^hello wo.*$/i');

    expect('Hello World')->not()->toMatch('/^hi$/i');
});

it('each', function () {
    expect([1, 2, 3])->each(fn ($number) => $number->toBeLessThan(4));
});

it('json', function () {
    $json = '{"name":"Nuno","credit":1000.00}';

    expect($json)
        ->json()
        ->toHaveCount(2)
        ->name->toBe('Nuno')
        ->credit->toBeFloat();
})->todo();
