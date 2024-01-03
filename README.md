# SWEW/test

## phar version [swew/test.phar](https://github.com/swew-app/test.phar)

A PHP test framework that solves the fatal flaw of all others.

This composer package is a lightweight and fast testing library designed to provide an informative interface. It offers a variety of testing features, including unit testing, integration testing, and functional testing. The package is designed to make testing simple and easy, allowing developers to quickly identify and fix bugs in their code. With its fast and efficient testing tools, developers can ensure that their code is reliable and performs well in real-world scenarios.

---

It's designed to help you write tests quickly and stick to TDD.

---

# Packages developed by SWEW

> - [swew/cli](https://packagist.org/packages/swew/cli) - A command-line interface program with formatting and text entry functions.
> - [swew/test](https://packagist.org/packages/swew/test) - A test framework that is designed to fix the fatal flaw of other test frameworks.
> - [swew/db](https://packagist.org/packages/swew/db) - A lightweight, fast, and secure PHP library for interacting with databases, creating migrations, and running queries.
> - [swew/dd](https://packagist.org/packages/swew/dd) - The simplest way to debug variables. As in Laravel.

---


# Quick start

## Installation

```sh
composer require --dev swew/test

composer exec t -- --init
```

Next to the `composer.json` file, a config file will be created for tests - `swew.json`.

Now you can start writing tests.

## Get started

All tests, by default, are in files with the ending `*.spec.php` or `*.test.php`. For example the path to the file may be as follows: `tests/Unit/string-utils.spec.php`.

To write the test itself, you only need a few functions.

| Name | Description |
|---|---|
`it` | container for the test case
`expect` | function, to check the test case, must be inside `it`

You may also need other functions:

| Name | Description |
|---|---|
`beforeAll` | Runs before the start of all tests in the file
`beforeEach` | Runs before each test in the file
`afterEach` | Runs after each test in the file
`afterAll` | Runs after all tests in the file

It is now possible to run in the console:

```sh
composer exec t
```


## Example

```php
<?php
// example.spec.php

declare(strict_types=1);

 it('Test 1', function () {
     expect(10)->not()->toBe(1);
 });

 it('Test 2: with dataset', function (int $num, int $n2 = 3) {
     $a = str_repeat("Hello", $num * 100000);

     return $a;
 })->with([
     1,
     [2, 3]
 ]);

 it('Test 3: skip', function () {
     sleep(2);
 })->skip();
```

---

License MIT.

