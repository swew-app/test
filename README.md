# SWEW/test

A PHP test framework that solves the fatal flaw of all others.

---

Another test framework, which aims to be fast, easy and simple.

It's designed to help you write tests quickly and stick to TDD.

# Quick start

## Installation

```sh
composer require --dev swew/test

php ./vendor/bin/test init
```

Next to the `composer.json` file, a config file will be created for tests - `swew-test.json`.

Now you can start writing tests.

## Get started

All tests, by default, are in files with the ending `*.spec.php` or `*.test.php`. For example the path to the file may be as follows: `tests/Unit/string-utils.spec.php`.

To write the test itself, you only need a few functions.

| Name | Description |
|---|---|
`it` | container for the test case
`expect` | function, to check the test case, must be inside `it`

Могут так же понадобится еще функции:

| Name | Description |
|---|---|
`beforeAll` | Runs before the start of all tests in the file
`beforeEach` | Runs before each test in the file
`afterEach` | Runs after each test in the file
`AfterAll` | Runs after all tests in the file

It is now possible to run in the console:

```sh
composer run test
```


