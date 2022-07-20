<?php

declare(strict_types=1);

namespace SWEW\Test\Expectations;

use SWEW\Test\Exceptions\Exception;
use SWEW\Test\Exceptions\ExpectException;
use SWEW\Test\TestRunner;
use Traversable;
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
        $suite = TestRunner::getCurrentSuite();

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

        return $this;
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
            if ($this->expectValue === $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: 'Expected a value identical:'
                );
            }
        } else {
            if ($this->expectValue !== $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: 'Expected a value not identical:'
                );
            }
        }

        return $this;
    }

    public function toBeArray(): self
    {
        if ($this->isNot) {
            if (is_array($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    'Not Array',
                    $this->message ?: 'Expected not an array.'
                );
            }
        } else {
            if (!is_array($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    'Array',
                    $this->message ?: 'Expected an array.'
                );
            }
        }

        return $this;
    }

    public function toBeEmpty(): self
    {
        if ($this->isNot) {
            if (empty($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    'Non-empty empty value',
                    $this->message ?: 'Expected a non-empty value.'
                );
            }
        } else {
            if (!empty($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    'Empty value',
                    $this->message ?: 'Expected an empty value.'
                );
            }
        }

        return $this;
    }

    public function toBeTrue(): self
    {
        if ($this->isNot) {
            if ($this->expectValue === true) {
                throw new ExpectException(
                    $this->expectValue,
                    false,
                    $this->message ?: 'Expected a value to be false.'
                );
            }
        } else {
            if ($this->expectValue !== true) {
                throw new ExpectException(
                    $this->expectValue,
                    true,
                    $this->message ?: 'Expected a value to be true.'
                );
            }
        }

        return $this;
    }

    public function toBeTruthy(): self
    {
        if ($this->isNot) {
            if (!empty($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    'Falsy value',
                    $this->message ?: 'Expected an falsy value.'
                );
            }
        } else {
            if (empty($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    'Truthy value',
                    $this->message ?: 'Expected a truthy value.'
                );
            }
        }

        return $this;
    }

    public function toBeFalse(): self
    {
        if ($this->isNot) {
            if ($this->expectValue === false) {
                throw new ExpectException(
                    $this->expectValue,
                    true,
                    $this->message ?: 'Expected a value to be true.'
                );
            }
        } else {
            if ($this->expectValue !== false) {
                throw new ExpectException(
                    $this->expectValue,
                    false,
                    $this->message ?: 'Expected a value to be false.'
                );
            }
        }

        return $this;
    }

    public function toBeFalsy(): self
    {
        if ($this->isNot) {
            if (empty($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    'Non-Falsy value',
                    $this->message ?: 'Expected a non-empty value.'
                );
            }
        } else {
            if (!empty($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    'Falsy value',
                    $this->message ?: 'Expected an empty value.'
                );
            }
        }

        return $this;
    }

    public function toBeGreaterThan(mixed $value): self
    {
        if ($this->isNot) {
            if ($this->expectValue >= $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value less than: $value"
                );
            }
        } else {
            if ($this->expectValue <= $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value greater than: $value"
                );
            }
        }

        return $this;
    }

    public function toBeGreaterThanOrEqual(mixed $value): self
    {
        if ($this->isNot) {
            if ($this->expectValue > $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value less than or equal to: $value"
                );
            }
        } else {
            if ($this->expectValue < $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value greater than or equal to: $value"
                );
            }
        }

        return $this;
    }

    public function toBeLessThan(mixed $value): self
    {
        if ($this->isNot) {
            if ($this->expectValue <= $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value greater than: $value"
                );
            }
        } else {
            if ($this->expectValue >= $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value less than: $value"
                );
            }
        }

        return $this;
    }

    public function toBeLessThanOrEqual(mixed $value): self
    {
        if ($this->isNot) {
            if ($this->expectValue < $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value greater than or equal to: $value"
                );
            }
        } else {
            if ($this->expectValue > $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value less than or equal to: $value"
                );
            }
        }

        return $this;
    }

    public function toContain(mixed $value): self
    {
        if ($this->isNot) {
            if (str_contains(strval($this->expectValue), strval($value))) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Was not expected to be contained in a value: '$value'"
                );
            }
        } else {
            if (!str_contains(strval($this->expectValue), strval($value))) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value to contain: '$value'"
                );
            }
        }

        return $this;
    }

    public function toHaveCount(int $number): self
    {
        if ($this->isNot) {
            if (count($this->expectValue) === $number) {
                throw new ExpectException(
                    $this->expectValue,
                    $number,
                    $this->message ?: "Expected an array to contain not $number elements"
                );
            }
        } else {
            if (count($this->expectValue) !== $number) {
                throw new ExpectException(
                    $this->expectValue,
                    $number,
                    $this->message ?: "Expected an array to contain $number elements"
                );
            }
        }

        return $this;
    }

    public function toHaveProperty(string $property): self
    {
        if ($this->isNot) {
            if (\property_exists($this->expectValue, $property)) {
                throw new ExpectException(
                    $this->expectValue,
                    $property,
                    $this->message ?: "Expected the property '$property' to not exist."
                );
            }
        } else {
            if (!\property_exists($this->expectValue, $property)) {
                throw new ExpectException(
                    $this->expectValue,
                    $property,
                    $this->message ?: "Expected the property '$property' to exist."
                );
            }
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
            if (!(isset($value) || \array_key_exists($key, $array))) {
                throw new ExpectException(
                    $this->expectValue,
                    $key,
                    $this->message ?: "Expected the key '$key' to exist."
                );
            }

            if ($value != $valueAsArray[$key]) {
                throw new ExpectException(
                    $value,
                    $valueAsArray[$key],
                    $this->message ?: "Failed asserting that an array has a key $key with the value $value."
                );
            }
        }

        return $this;
    }

    public function toMatchObject(mixed $object): self
    {
        if ($this->isNot) {
            if ($this->expectValue == $object) {
                throw new ExpectException(
                    $this->expectValue,
                    $object,
                    $this->message ?: "Expected a different value than"
                );
            }
        } else {
            if ($this->expectValue != $object) {
                throw new ExpectException(
                    $this->expectValue,
                    $object,
                    $this->message ?: "Expected a value equal"
                );
            }
        }

        return $this;
    }

    public function toEqual(mixed $value): self
    {
        if ($this->isNot) {
            if ($this->expectValue == $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a different value than"
                );
            }
        } else {
            if ($this->expectValue != $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: "Expected a value equal"
                );
            }
        }

        return $this;
    }

    public function toEqualWithDelta(float $min, float $max): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toEqualWithDelta" cannot be used with "not()"');
        }

        $maxVal = $min + $max;

        if ($this->expectValue < $min) {
            throw new ExpectException(
                $this->expectValue,
                $min,
                $this->message ?: "Expected a value between $min and $maxVal."
            );
        }
        if ($this->expectValue > $maxVal) {
            throw new ExpectException(
                $this->expectValue,
                $maxVal,
                $this->message ?: "Expected a value between $min and $maxVal."
            );
        }

        return $this;
    }

    public function toBeIn(array $array): self
    {
        if ($this->isNot) {
            if (\in_array($this->expectValue, $array, true)) {
                throw new ExpectException(
                    $array,
                    '',
                    $this->message ?: "Expected not to be in the array: " . $this->expectValue
                );
            }
        } else {
            if (!\in_array($this->expectValue, $array, true)) {
                throw new ExpectException(
                    $array,
                    '',
                    $this->message ?: "Expected availability: " . $this->expectValue
                );
            }
        }

        return $this;
    }

    public function toBeInstanceOf(mixed $class): self
    {
        if ($this->isNot) {
            if ($this->expectValue instanceof $class) {
                throw new ExpectException(
                    $this->expectValue,
                    $class,
                    $this->message ?: 'Expected an instance other'
                );
            }
        } else {
            if (!($this->expectValue instanceof $class)) {
                throw new ExpectException(
                    $this->expectValue,
                    $class,
                    $this->message ?: 'Expected an instance of'
                );
            }
        }

        return $this;
    }

    public function toBeBool(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeBool" cannot be used with "not()"');
        }

        if (!\is_bool($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: 'Expected a boolean.'
            );
        }

        return $this;
    }

    public function toBeCallable(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeCallable" cannot be used with "not()"');
        }

        if (!\is_callable($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                'Callable',
                $this->message ?: 'Expected a callable.'
            );
        }

        return $this;
    }

    public function toBeFloat(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeFloat" cannot be used with "not()"');
        }

        if (!\is_float($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: 'Expected a float.'
            );
        }

        return $this;
    }

    public function toBeInt(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeInt" cannot be used with "not()"');
        }

        if (!\is_int($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: 'Expected an integer.'
            );
        }

        return $this;
    }

    public function toBeIterable(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeIterable" cannot be used with "not()"');
        }

        if (!\is_array($this->expectValue) && !($this->expectValue instanceof Traversable)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: 'Expected an iterable.'
            );
        }

        return $this;
    }

    public function toBeNumeric(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeNumeric" cannot be used with "not()"');
        }

        if (!\is_numeric($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: 'Expected a numeric.'
            );
        }

        return $this;
    }

    public function toBeObject(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeObject" cannot be used with "not()"');
        }

        if (!\is_object($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: 'Expected an object.'
            );
        }

        return $this;
    }

    public function toBeResource(mixed $type = null): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeResource" cannot be used with "not()"');
        }

        if (!\is_resource($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: 'Expected a resource.'
            );
        }

        if ($type && $type !== \get_resource_type($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: "Expected a resource of type $type"
            );
        }

        return $this;
    }

    public function toBeScalar(): self
    {
        if ($this->isNot) {
            if (\is_scalar($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: 'Expected a non-scalar.'
                );
            }
        } else {
            if (!\is_scalar($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: 'Expected a scalar.'
                );
            }
        }

        return $this;
    }

    public function toBeString(): self
    {
        if ($this->isNot) {
            if (\is_string($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: 'Expected a non-string.'
                );
            }
        } else {
            if (!\is_string($this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: 'Expected a string.'
                );
            }
        }

        return $this;
    }

    public function toBeJson(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeJson" cannot be used with "not()"');
        }

        if (!\is_string($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: 'Expected a JSON string.'
            );
        }

        $json = json_decode($this->expectValue, true);

        if (is_null($json)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: 'Expected JSON'
            );
        }

        return $this;
    }

    public function toBeNull(): self
    {
        if ($this->isNot) {
            if (null === $this->expectValue) {
                throw new ExpectException(
                    $this->expectValue,
                    null,
                    $this->message ?: 'Expected a value other than null.'
                );
            }
        } else {
            if (null !== $this->expectValue) {
                throw new ExpectException(
                    $this->expectValue,
                    null,
                    $this->message ?: 'Expected null.'
                );
            }
        }

        return $this;
    }

    public function toHaveKey(string|int $key): self
    {
        if ($this->isNot) {
            if (isset($this->expectValue[$key]) || \array_key_exists($key, $this->expectValue)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: "Expected the key '$key' to not exist."
                );
            }
        } else {
            if (!(isset($this->expectValue[$key]) || \array_key_exists($key, $this->expectValue))) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: "Expected the key '$key' to exist"
                );
            }
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
            if (count($this->expectValue) !== $length) {
                throw new ExpectException(
                    count($this->expectValue),
                    $length,
                    $this->message ?: "Expected an array to contain $length elements"
                );
            }
        } else {
            if ($length !== strlen($this->expectValue)) {
                throw new ExpectException(
                    strlen($this->expectValue),
                    $length,
                    $this->message ?: "Expected a value to contain $length characters"
                );
            }
        }

        return $this;
    }

    public function toBeReadableDirectory(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeReadableDirectory" cannot be used with "not()"');
        }

        if (!\is_readable($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: "The path is not readable."
            );
        }

        return $this;
    }

    public function toBeWritableDirectory(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeWritableDirectory" cannot be used with "not()"');
        }

        if (!\is_writable($this->expectValue)) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: "The path is not writable."
            );
        }

        return $this;
    }

    public function toStartWith(string $str): self
    {
        if ($this->isNot) {
            if (str_starts_with($this->expectValue, $str)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: "Expected a value not to start with '$str'"
                );
            }
        } else {
            if (!str_starts_with($this->expectValue, $str)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: "Expected a value to start with '$str'"
                );
            }
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
                if ($e instanceof $class) {
                    throw new ExpectException(
                        get_class($e),
                        '',
                        $this->message ?: "Expected an instance other than $class"
                    );
                }
            }

            return $this;
        } // END NOT

        if (class_exists($class, false)) {
            try {
                $expression = $this->expectValue;
                $expression();
            } catch (Exception|\Throwable $e) {
                /** @var class-string<Exception> $class */
                if (!($e instanceof $class)) {
                    throw new ExpectException(
                        get_class($e),
                        $class,
                        $this->message ?: "Expected to throw $class"
                    );
                }
            }
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

            if ($errorMessage !== $message) {
                throw new ExpectException(
                    $errorMessage,
                    $message,
                    $this->message ?: "Expect the messages to be identical"
                );
            }
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
