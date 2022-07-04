<?php

declare(strict_types=1);

namespace SWEW\Test\Expectations;

use SWEW\Test\Exceptions\Exception;
use SWEW\Test\Runner\TestManager;
use Webmozart\Assert\Assert;

/**
 * @property Expectation $not
 */
final class Expectation
{
    private bool $isNot = false;

    public function __construct(
        private readonly mixed $expectValue,
        private string         $message = ''
    ) {
        $suite = TestManager::getCurrentSuite();

        if (!is_null($suite)) {
            $suite->stopLogData();
        }
    }

    public function __get(string $name): self
    {
        if ($name !== 'not') {
            trigger_error('Call unimplemented property');
        }

        $this->isNot = true;

        return  $this;
    }

    public function not(): self
    {
        $this->isNot = true;

        return $this;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function toBe(mixed $value): self
    {
        if ($this->isNot) {
            Assert::notSame($this->expectValue, $value, $this->message);
        } else {
            Assert::same($this->expectValue, $value, $this->message);
        }

        return $this;
    }

    public function toBeArray(): self
    {
        if ($this->isNot) {
            Assert::notEq(gettype($this->expectValue), 'array', $this->message);
        } else {
            Assert::isArray($this->expectValue, $this->message);
        }

        return $this;
    }

    public function toBeEmpty(): self
    {
        if ($this->isNot) {
            Assert::notEmpty($this->expectValue, $this->message);
        } else {
            Assert::isEmpty($this->expectValue, $this->message);
        }

        return $this;
    }

    public function toBeTrue(): self
    {
        if ($this->isNot) {
            Assert::false($this->expectValue, $this->message);
        } else {
            Assert::true($this->expectValue, $this->message);
        }

        return $this;
    }

    public function toBeTruthy(): self
    {
        if ($this->isNot) {
            Assert::false(!!$this->expectValue, $this->message);
        } else {
            Assert::true(!!$this->expectValue, $this->message);
        }

        return $this;
    }

    public function toBeFalse(): self
    {
        if ($this->isNot) {
            Assert::true($this->expectValue, $this->message);
        } else {
            Assert::false($this->expectValue, $this->message);
        }

        return $this;
    }

    public function toBeFalsy(): self
    {
        if ($this->isNot) {
            Assert::false(!$this->expectValue, $this->message);
        } else {
            Assert::true(!$this->expectValue, $this->message);
        }

        return $this;
    }

    public function toBeGreaterThan(mixed $value): self
    {
        if ($this->isNot) {
            Assert::lessThan($this->expectValue, $value, $this->message);
        } else {
            Assert::greaterThan($this->expectValue, $value, $this->message);
        }

        return $this;
    }

    public function toBeGreaterThanOrEqual(mixed $value): self
    {
        if ($this->isNot) {
            Assert::lessThanEq($this->expectValue, $value, $this->message);
        } else {
            Assert::greaterThanEq($this->expectValue, $value, $this->message);
        }

        return $this;
    }

    public function toBeLessThan(mixed $value): self
    {
        if ($this->isNot) {
            Assert::greaterThan($this->expectValue, $value, $this->message);
        } else {
            Assert::lessThan($this->expectValue, $value, $this->message);
        }

        return $this;
    }

    public function toBeLessThanOrEqual(mixed $value): self
    {
        if ($this->isNot) {
            Assert::greaterThanEq($this->expectValue, $value, $this->message);
        } else {
            Assert::lessThanEq($this->expectValue, $value, $this->message);
        }

        return $this;
    }

    public function toContain(mixed $value): self
    {
        if ($this->isNot) {
            Assert::notContains($this->expectValue, $value, $this->message);
        } else {
            Assert::contains($this->expectValue, $value, $this->message);
        }

        return $this;
    }

    public function toHaveCount(int $number): self
    {
        if ($this->isNot) {
            Assert::notEq(
                \count($this->expectValue),
                $number,
                \sprintf(
                    $this->message ?: 'Expected an array to contain %d elements. Got: %d.',
                    $number,
                    \count($this->expectValue)
                )
            );
        } else {
            Assert::count($this->expectValue, $number, $this->message);
        }

        return $this;
    }

    public function toHaveProperty(string $property): self
    {
        if ($this->isNot) {
            Assert::propertyNotExists($this->expectValue, $property, $this->message);
        } else {
            Assert::propertyExists($this->expectValue, $property, $this->message);
        }

        return $this;
    }

    public function toMatchArray(array $array): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toMatchArray" cannot be used with "not()"');
        }

        if (is_object($this->expectValue) && method_exists($this->expectValue, 'toArray')) {
            $valueAsArray = $this->expectValue->toArray();
        } else {
            $valueAsArray = (array)$this->expectValue;
        }

        foreach ($array as $key => $value) {
            Assert::keyExists($valueAsArray, $key);

            Assert::eq(
                $value,
                $valueAsArray[$key],
                sprintf(
                    'Failed asserting that an array has a key %s with the value %s.',
                    $key,
                    $valueAsArray[$key],
                ),
            );
        }

        return $this;
    }

    public function toMatchObject(mixed $object): self
    {
        if ($this->isNot) {
            Assert::notEq($this->expectValue, $object, $this->message);
        } else {
            Assert::eq($this->expectValue, $object, $this->message);
        }

        return $this;
    }

    public function toEqual(mixed $value): self
    {
        if ($this->isNot) {
            Assert::notEq($this->expectValue, $value, $this->message);
        } else {
            Assert::eq($this->expectValue, $value, $this->message);
        }

        return $this;
    }

    public function toEqualWithDelta(float $min, float $max): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toEqualWithDelta" cannot be used with "not()"');
        }

        Assert::range($this->expectValue, $min, $min + $max, $this->message);

        return $this;
    }

    public function toBeIn(array $array): self
    {
        if ($this->isNot) {
            if (\in_array($this->expectValue, $array, true)) {
                throw new Exception($this->message);
            }
        } else {
            Assert::inArray($this->expectValue, $array, $this->message);
        }

        return $this;
    }

    public function toBeInstanceOf(mixed $class): self
    {
        if ($this->isNot) {
            Assert::notInstanceOf($this->expectValue, $class, $this->message);
        } else {
            Assert::isInstanceOf($this->expectValue, $class, $this->message);
        }

        return $this;
    }

    public function toBeBool(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeBool" cannot be used with "not()"');
        }

        Assert::boolean($this->expectValue, $this->message);

        return $this;
    }

    public function toBeCallable(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeCallable" cannot be used with "not()"');
        }

        Assert::isCallable($this->expectValue, $this->message);

        return $this;
    }

    public function toBeFloat(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeFloat" cannot be used with "not()"');
        }

        Assert::float($this->expectValue, $this->message);

        return $this;
    }

    public function toBeInt(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeInt" cannot be used with "not()"');
        }

        Assert::integer($this->expectValue, $this->message);

        return $this;
    }

    public function toBeIterable(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeIterable" cannot be used with "not()"');
        }

        Assert::isIterable($this->expectValue, $this->message);

        return $this;
    }

    public function toBeNumeric(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeNumeric" cannot be used with "not()"');
        }

        Assert::numeric($this->expectValue, $this->message);

        return $this;
    }

    public function toBeObject(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeObject" cannot be used with "not()"');
        }

        Assert::object($this->expectValue, $this->message);

        return $this;
    }

    public function toBeResource(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeResource" cannot be used with "not()"');
        }

        Assert::resource($this->expectValue, $this->message);

        return $this;
    }

    public function toBeScalar(): self
    {
        if ($this->isNot) {
            if (\is_scalar($this->expectValue)) {
                throw new Exception($this->message);
            }
        } else {
            Assert::scalar($this->expectValue, $this->message);
        }

        return $this;
    }

    public function toBeString(): self
    {
        if ($this->isNot) {
            if (\is_string($this->expectValue)) {
                throw new Exception($this->message);
            }
        } else {
            Assert::string($this->expectValue, $this->message);
        }

        return $this;
    }

    public function toBeJson(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeJson" cannot be used with "not()"');
        }

        Assert::string($this->expectValue, $this->message);

        $json = json_decode($this->expectValue, true);

        if (is_null($json)) {
            throw new Exception(
                \sprintf(
                    $this->message ?: 'Expected JSON. Got: %s',
                    $this->expectValue
                )
            );
        }

        return $this;
    }

    public function toBeNull(): self
    {
        if ($this->isNot) {
            Assert::notNull($this->expectValue, $this->message);
        } else {
            Assert::null($this->expectValue, $this->message);
        }

        return $this;
    }

    public function toHaveKey(string|int $key): self
    {
        if ($this->isNot) {
            Assert::keyNotExists($this->expectValue, $key, $this->message);
        } else {
            Assert::keyExists($this->expectValue, $key, $this->message);
        }

        return $this;
    }

    public function toHaveKeys(array $keys): self
    {
        foreach ($keys as $key) {
            $this->toHaveKey($key);
        }

        return $this;
    }

    public function toHaveLength(int $length): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toHaveLength" cannot be used with "not()"');
        }

        if (is_array($this->expectValue)) {
            Assert::count($this->expectValue, $length, $this->message);
        } else {
            Assert::length($this->expectValue, $length, $this->message);
        }

        return $this;
    }

    public function toBeReadableDirectory(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeReadableDirectory" cannot be used with "not()"');
        }

        Assert::readable($this->expectValue, $this->message);

        return $this;
    }

    public function toBeWritableDirectory(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeWritableDirectory" cannot be used with "not()"');
        }

        Assert::writable($this->expectValue, $this->message);

        return $this;
    }

    public function toStartWith(string $str): self
    {
        if ($this->isNot) {
            Assert::notStartsWith($this->expectValue, $str, $this->message);
        } else {
            Assert::startsWith($this->expectValue, $str, $this->message);
        }

        return $this;
    }

    public function toThrow(string $class = 'Exception', string $message = ''): self
    {
        if ($this->isNot) {
            try {
                $expression = $this->expectValue;
                $expression();
            } catch (Exception|\Throwable $e) {
                /** @var class-string<Exception> $class */
                Assert::notInstanceOf($e, $class, $this->message);
            }

            return $this;
        } // END NOT

        if (class_exists($class, false)) {
            /** @var class-string<Exception> $class */
            Assert::throws($this->expectValue, $class, $this->message);
        } elseif ($message === '') {
            $message = $class;
        }

        if ($message !== '') {
            $errorMessage = '';

            try {
                $expression = $this->expectValue;
                $expression();
            } catch (Exception|\Throwable $e) {
                $errorMessage = $e->getMessage();
            }

            Assert::same($message, $errorMessage, $this->message);
        }

        return $this;
    }

    public function toEndWith(string $str): self
    {
        if ($this->isNot) {
            Assert::notEndsWith($this->expectValue, $str, $this->message);
        } else {
            Assert::endsWith($this->expectValue, $str, $this->message);
        }

        return $this;
    }

    public function toMatch(string $pattern): self
    {
        if ($this->isNot) {
            Assert::notRegex($this->expectValue, $pattern, $this->message);
        } else {
            Assert::regex($this->expectValue, $pattern, $this->message);
        }

        return $this;
    }

    public function each(callable $callback = null): self
    {
        if ($this->isNot) {
            throw new Exception('The method "each" cannot be used with "not()"');
        }

        if (!is_iterable($this->expectValue)) {
            throw new Exception('Expectation value is not iterable.');
        }

        if (is_callable($callback)) {
            foreach ($this->expectValue as $item) {
                $callback(expect($item));
            }
        }

        return $this;
    }

    public function json(): self
    {
        throw new Exception('// TODO');
        return $this;
    }

    public function sequence(): self
    {
        throw new Exception('// TODO');
        return $this;
    }

    public function unless(): self
    {
        throw new Exception('// TODO');
        return $this;
    }

    public function when(): self
    {
        throw new Exception('// TODO');
        return $this;
    }
}
