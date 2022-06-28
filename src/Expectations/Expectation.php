<?php

declare(strict_types=1);

namespace SWEW\Test\Expectations;

use SWEW\Test\Exceptions\Exception;
use SWEW\Test\Runner\TestManager;
use Webmozart\Assert\Assert;

final class Expectation
{
    private bool $isNot = false;

    function __construct(
        private readonly mixed $expectValue,
        private string $message = ''
    ) {
        $suite = TestManager::getCurrentSuite();
        $suite->stopLogData();
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

    public function toContain(): self
    {
        // TODO
        return $this;
    }

    public function toHaveCount(): self
    {
        // TODO
        return $this;
    }

    public function toHaveProperty(): self
    {
        // TODO
        return $this;
    }

    public function toHaveProperties(): self
    {
        // TODO
        return $this;
    }

    public function toMatchArray(): self
    {
        // TODO
        return $this;
    }

    public function toMatchObject(): self
    {
        // TODO
        return $this;
    }

    public function toEqual(): self
    {
        // TODO
        return $this;
    }

    public function toEqualCanonicalizing(): self
    {
        // TODO
        return $this;
    }

    public function toEqualWithDelta(): self
    {
        // TODO
        return $this;
    }

    public function toBeIn(): self
    {
        // TODO
        return $this;
    }

    public function toBeInfinite(): self
    {
        // TODO
        return $this;
    }

    public function toBeInstanceOf(): self
    {
        // TODO
        return $this;
    }

    public function toBeBool(): self
    {
        // TODO
        return $this;
    }

    public function toBeCallable(): self
    {
        // TODO
        return $this;
    }

    public function toBeFloat(): self
    {
        // TODO
        return $this;
    }

    public function toBeInt(): self
    {
        // TODO
        return $this;
    }

    public function toBeIterable(): self
    {
        // TODO
        return $this;
    }

    public function toBeNumeric(): self
    {
        // TODO
        return $this;
    }

    public function toBeObject(): self
    {
        // TODO
        return $this;
    }

    public function toBeResource(): self
    {
        // TODO
        return $this;
    }

    public function toBeScalar(): self
    {
        // TODO
        return $this;
    }

    public function toBeString(): self
    {
        // TODO
        return $this;
    }

    public function toBeJson(): self
    {
        // TODO
        return $this;
    }

    public function toBeNan(): self
    {
        // TODO
        return $this;
    }

    public function toBeNull(): self
    {
        // TODO
        return $this;
    }

    public function toHaveKey(): self
    {
        // TODO
        return $this;
    }

    public function toHaveKeys(): self
    {
        // TODO
        return $this;
    }

    public function toHaveLength(): self
    {
        // TODO
        return $this;
    }

    public function toBeReadableDirectory(): self
    {
        // TODO
        return $this;
    }

    public function toBeWritableDirectory(): self
    {
        // TODO
        return $this;
    }

    public function toStartWith(): self
    {
        // TODO
        return $this;
    }

    public function toThrow(): self
    {
        // TODO
        return $this;
    }

    public function toEndWith(): self
    {
        // TODO
        return $this;
    }

    public function toMatch(): self
    {
        // TODO
        return $this;
    }

    public function toMatchConstraint(): self
    {
        // TODO
        return $this;
    }

    public function dd(...$arguments): Expectation
    {
        if (function_exists('dd')) {
            return $this;
            dd($this->value, ...$arguments);
        }

        var_dump($this->value);

        exit(1);
    }

    public function each(): self
    {
        // TODO
        return $this;
    }

    public function json(): self
    {
        // TODO
        return $this;
    }

    public function sequence(): self
    {
        // TODO
        return $this;
    }

    public function unless(): self
    {
        // TODO
        return $this;
    }

    public function when(): self
    {
        // TODO
        return $this;
    }
}
