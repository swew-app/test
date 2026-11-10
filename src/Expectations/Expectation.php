<?php

declare(strict_types=1);

namespace Swew\Test\Expectations;

use Closure;
use Swew\Test\Exceptions\Exception;
use Swew\Test\Exceptions\ExpectException;
use Swew\Test\Suite\SuiteGroup;
use Traversable;

/**
 * @template TValue
 * @property Expectation<TValue> $not
 */
final class Expectation
{
    private bool $isNot = false;

    private bool $isNeedDump = false;

    /**
     * @var array <string, Closure>
     */
    private static array $extends = [];

    /** @param TValue $expectValue */
    public function __construct(
        private readonly mixed $expectValue,
        private string $message = ''
    ) {
        $suite = SuiteGroup::getCurrentSuite();

        if (!is_null($suite)) {
            $suite->stopLogData();
        }
    }

    public function __destruct()
    {
        if ($this->isNeedDump) {
            __dump($this->expectValue);
        }
    }

    public function __get(string $name): self
    {
        if ($name === 'not') {
            $this->isNot = true;
            return $this;
        }

        throw new Exception("Unknown expectation property: $name");
    }

    public function __call(string $name, array $arguments): self
    {
        if (array_key_exists($name, self::$extends)) {
            /** @var Closure $fn */
            $fn = self::$extends[$name];

            $boundFn = $fn->bindTo($this);

            if (is_callable($boundFn)) {
                $boundFn($this->expectValue, ...$arguments);
            } else {
                throw new Exception("Cannot call expectation: $name");
            }
        } else {
            throw new Exception("Unknown expectation: $name");
        }

        return $this;
    }

    public function extend(string $name, Closure $extend): void
    {
        self::$extends[$name] = $extend;
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
                throw new ExpectException($this->expectValue, $value, $this->message ?: 'Expected a value identical:');
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
                throw new ExpectException($this->expectValue, 'Not Array', $this->message ?: 'Expected not an array.');
            }
        } else {
            if (!is_array($this->expectValue)) {
                throw new ExpectException($this->expectValue, 'Array', $this->message ?: 'Expected an array.');
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
                throw new ExpectException($this->expectValue, false, $this->message ?: 'Expected a value to be false.');
            }
        } else {
            if ($this->expectValue !== true) {
                throw new ExpectException($this->expectValue, true, $this->message ?: 'Expected a value to be true.');
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
                throw new ExpectException($this->expectValue, true, $this->message ?: 'Expected a value to be true.');
            }
        } else {
            if ($this->expectValue !== false) {
                throw new ExpectException($this->expectValue, false, $this->message ?: 'Expected a value to be false.');
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

    /**
     * @template TExpected
     * @param TExpected $value
     */
    public function toBeGreaterThan(mixed $value): self
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

    /**
     * @template TExpected
     * @param TExpected $value
     */
    public function toBeGreaterThanOrEqual(mixed $value): self
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

    /**
     * @template TExpected
     * @param TExpected $value
     */
    public function toBeLessThan(mixed $value): self
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

    /**
     * @template TExpected
     * @param TExpected $value
     */
    public function toBeLessThanOrEqual(mixed $value): self
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
        $value = $this->expectValue;
        if (!is_countable($value)) {
            throw new ExpectException($value, 'Countable', $this->message ?: 'Expected an array or Countable.');
        }
        $count = count($value);

        if ($this->isNot) {
            if ($count === $number) {
                throw new ExpectException(
                    $count,
                    $number,
                    $this->message ?: "Expected an array to contain not $number elements"
                );
            }
        } else {
            if ($count !== $number) {
                throw new ExpectException(
                    $count,
                    $number,
                    $this->message ?: "Expected an array to contain $number elements"
                );
            }
        }

        return $this;
    }

    public function toHaveProperty(string $property): self
    {
        $value = $this->expectValue;
        if (!is_object($value) && !( is_string($value) && class_exists($value) )) {
            throw new ExpectException(
                $value,
                'Object or class',
                $this->message ?: 'Expected an object or existing class.'
            );
        }
        /** @var object|class-string $value */

        if ($this->isNot) {
            if (\property_exists($value, $property)) {
                throw new ExpectException(
                    $this->expectValue,
                    $property,
                    $this->message ?: "Expected the property '$property' to not exist."
                );
            }
        } else {
            if (!\property_exists($value, $property)) {
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

        $value = $this->expectValue;
        if (is_object($value) && method_exists($value, 'toArray')) {
            $valueAsArray = $value->toArray();
        } elseif (is_array($value) || is_object($value)) {
            /** @var array|object $value */
            $valueAsArray = (array) $value;
        } else {
            throw new ExpectException($value, 'Array or object', $this->message ?: 'Expected an array or object.');
        }
        if (!is_array($valueAsArray)) {
            throw new ExpectException(
                $valueAsArray,
                'Array',
                $this->message ?: 'Expected toArray() to return an array.'
            );
        }

        foreach ($array as $key => $value) {
            if (!array_key_exists($key, $valueAsArray)) {
                $keys = array_keys($valueAsArray);
                $keyCount = count($keys);
                $keyList =
                    "\n  has keys:\n  ["
                    . implode('', array_map(fn($k) => "\n   '$k',", array_slice($keys, 0, 9)))
                    . ( $keyCount > 9 ? "\n   ..." : '' )
                    . "\n  ]";

                throw new ExpectException(
                    $this->expectValue,
                    $key,
                    $this->message ?: "Expected the key '$key' to exist." . $keyList
                );
            }

            if ($value !== $valueAsArray[$key]) {
                throw new ExpectException(
                    $valueAsArray,
                    $array,
                    $this->message ?: "Failed asserting that an array has a matching value for key '$key'."
                );
            }
        }

        return $this;
    }

    /**
     * @template TExpected
     * @param TExpected $object
     */
    public function toMatchObject(mixed $object): self
    {
        if ($this->isNot) {
            if ($this->expectValue == $object) {
                throw new ExpectException(
                    $this->expectValue,
                    $object,
                    $this->message ?: 'Expected a different value than'
                );
            }
        } else {
            if ($this->expectValue != $object) {
                throw new ExpectException($this->expectValue, $object, $this->message ?: 'Expected a value equal');
            }
        }

        return $this;
    }

    /**
     * @template TExpected
     * @param TExpected $value
     */
    public function toEqual(mixed $value): self
    {
        if ($this->isNot) {
            if ($this->expectValue == $value) {
                throw new ExpectException(
                    $this->expectValue,
                    $value,
                    $this->message ?: 'Expected a different value than'
                );
            }
        } else {
            if ($this->expectValue != $value) {
                throw new ExpectException($this->expectValue, $value, $this->message ?: 'Expected a value equal');
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
                    $this->expectValue,
                    $array,
                    $this->message ?: 'Expected the value not to be in the array.'
                );
            }
        } else {
            if (!\in_array($this->expectValue, $array, true)) {
                throw new ExpectException(
                    $this->expectValue,
                    $array,
                    $this->message ?: 'Expected the value to be in the array.'
                );
            }
        }

        return $this;
    }

    public function toBeInstanceOf(mixed $class): self
    {
        if (!is_string($class) || !class_exists($class) && !interface_exists($class)) {
            throw new ExpectException($class, '', 'An invalid value is passed');
        }

        if ($this->isNot) {
            if ($this->expectValue instanceof $class) {
                throw new ExpectException($this->expectValue, $class, $this->message ?: 'Expected an instance other');
            }
        } else {
            if (!$this->expectValue instanceof $class) {
                throw new ExpectException($this->expectValue, $class, $this->message ?: 'Expected an instance of');
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
            throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected a boolean.');
        }

        return $this;
    }

    public function toBeCallable(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeCallable" cannot be used with "not()"');
        }

        if (!\is_callable($this->expectValue)) {
            throw new ExpectException($this->expectValue, 'Callable', $this->message ?: 'Expected a callable.');
        }

        return $this;
    }

    public function toBeFloat(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeFloat" cannot be used with "not()"');
        }

        if (!\is_float($this->expectValue)) {
            throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected a float.');
        }

        return $this;
    }

    public function toBeInt(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeInt" cannot be used with "not()"');
        }

        if (!\is_int($this->expectValue)) {
            throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected an integer.');
        }

        return $this;
    }

    public function toBeIterable(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeIterable" cannot be used with "not()"');
        }

        if (!\is_array($this->expectValue) && !$this->expectValue instanceof Traversable) {
            throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected an iterable.');
        }

        return $this;
    }

    public function toBeNumeric(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeNumeric" cannot be used with "not()"');
        }

        if (!\is_numeric($this->expectValue)) {
            throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected a numeric.');
        }

        return $this;
    }

    public function toBeObject(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeObject" cannot be used with "not()"');
        }

        if (!\is_object($this->expectValue)) {
            throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected an object.');
        }

        return $this;
    }

    public function toBeResource(?string $type = null): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeResource" cannot be used with "not()"');
        }

        if (!\is_resource($this->expectValue)) {
            throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected a resource.');
        }

        if ($type && $type !== \get_resource_type($this->expectValue)) {
            throw new ExpectException($this->expectValue, '', $this->message ?: "Expected a resource of type $type");
        }

        return $this;
    }

    public function toBeScalar(): self
    {
        if (is_scalar($this->expectValue) === $this->isNot) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message ?: ( $this->isNot ? 'Expected a non-scalar.' : 'Expected a scalar.' )
            );
        }

        return $this;
    }

    public function toBeString(): self
    {
        if ($this->isNot) {
            if (\is_string($this->expectValue)) {
                throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected a non-string.');
            }
        } else {
            if (!\is_string($this->expectValue)) {
                throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected a string.');
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
            throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected a JSON string.');
        }

        json_decode($this->expectValue, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ExpectException($this->expectValue, '', $this->message ?: 'Expected JSON');
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
                throw new ExpectException($this->expectValue, null, $this->message ?: 'Expected null.');
            }
        }

        return $this;
    }

    public function toHaveKey(string|int $key): self
    {
        $value = $this->expectValue;
        if (!is_array($value)) {
            throw new ExpectException($value, 'Array', $this->message ?: 'Expected an array.');
        }

        if ($this->isNot) {
            if (isset($value[$key]) || \array_key_exists($key, $value)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: "Expected the key '$key' to not exist."
                );
            }
        } else {
            if (!( isset($value[$key]) || \array_key_exists($key, $value) )) {
                throw new ExpectException($this->expectValue, '', $this->message ?: "Expected the key '$key' to exist");
            }
        }

        return $this;
    }

    /** @param array<int|string> $keys */
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
            if ($length !== mb_strlen($this->stringValue(), 'UTF-8')) {
                throw new ExpectException(
                    mb_strlen($this->stringValue(), 'UTF-8'),
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

        if (!\is_readable($this->stringValue())) {
            throw new ExpectException($this->expectValue, '', $this->message ?: 'The path is not readable.');
        }

        return $this;
    }

    public function toBeWritableDirectory(): self
    {
        if ($this->isNot) {
            throw new Exception('The method "toBeWritableDirectory" cannot be used with "not()"');
        }

        if (!\is_writable($this->stringValue())) {
            throw new ExpectException($this->expectValue, '', $this->message ?: 'The path is not writable.');
        }

        return $this;
    }

    public function toStartWith(string $prefix): self
    {
        if ($this->isNot) {
            if (str_starts_with($this->stringValue(), $prefix)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: "Expected a value not to start with '$prefix'"
                );
            }
        } else {
            if (!str_starts_with($this->stringValue(), $prefix)) {
                throw new ExpectException(
                    $this->expectValue,
                    '',
                    $this->message ?: "Expected a value to start with '$prefix'"
                );
            }
        }

        return $this;
    }

    public function toThrow(string $class = 'Exception', string $message = ''): self
    {
        if (!is_callable($this->expectValue)) {
            throw new Exception('The method "toThrow" requires a callable.');
        }

        $isClass = class_exists($class) || interface_exists($class);
        if (!$isClass && $message === '') {
            $message = $class;
        }

        $exception = null;
        try {
            ( $this->expectValue )();
        } catch (\Throwable $e) {
            $exception = $e;
        }

        $matches =
            $exception !== null
            && ( !$isClass || $exception instanceof $class )
            && ( $message === '' || $exception->getMessage() === $message );

        if ($matches === $this->isNot) {
            throw new ExpectException(
                $exception === null ? 'No exception' : get_class($exception) . ': ' . $exception->getMessage(),
                $isClass ? "$class: $message" : $message,
                $this->message
                ?: (
                    $this->isNot
                        ? 'Expected not to throw the matching exception.'
                        : 'Expected to throw the matching exception.'
                )
            );
        }

        return $this;
    }

    public function toEndWith(string $suffix): self
    {
        if ($this->isNot) {
            if (str_ends_with($this->stringValue(), $suffix)) {
                throw new ExpectException(
                    $this->expectValue,
                    $suffix,
                    $this->message ?: "Expected a value not to end with '$suffix'"
                );
            }
        } else {
            if (!str_ends_with($this->stringValue(), $suffix)) {
                throw new ExpectException(
                    $this->expectValue,
                    $suffix,
                    $this->message ?: 'Expected a value to end with'
                );
            }
        }

        return $this;
    }

    public function toMatch(string $pattern): self
    {
        if ($pattern === '') {
            throw new Exception('The regular expression cannot be empty.');
        }

        $matches = @preg_match($pattern, $this->stringValue());
        if ($matches === false) {
            throw new Exception('Invalid regular expression: ' . preg_last_error_msg());
        }

        if (( $matches === 1 ) === $this->isNot) {
            throw new ExpectException(
                $this->expectValue,
                '',
                $this->message
                ?: (
                    $this->isNot
                        ? "The value matches the pattern $pattern."
                        : "The value does not match the expected pattern: $pattern"
                )
            );
        }

        return $this;
    }

    public function each(callable $callback): self
    {
        if ($this->isNot) {
            throw new Exception('The method "each" cannot be used with "not()"');
        }

        if (!is_iterable($this->expectValue)) {
            throw new Exception('Expectation value is not iterable.');
        }

        foreach ($this->expectValue as $item) {
            $callback(expect($item));
        }

        return $this;
    }

    private function stringValue(): string
    {
        $value = $this->expectValue;
        if (!is_string($value)) {
            throw new ExpectException($value, 'String', $this->message ?: 'Expected a string.');
        }

        return $value;
    }

    public function dd(): self
    {
        $this->isNeedDump = true;

        return $this;
    }

    public function json(): self
    {
        throw new Exception('// TODO');
        return $this;
    }

    //    public function sequence(): self
    //    {
    //        throw new Exception('// TODO');
    //        return $this;
    //    }
    //
    //    public function unless(): self
    //    {
    //        throw new Exception('// TODO');
    //        return $this;
    //    }
    //
    //    public function when(): self
    //    {
    //        throw new Exception('// TODO');
    //        return $this;
    //    }
}
