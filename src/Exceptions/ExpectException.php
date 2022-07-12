<?php

declare(strict_types=1);

namespace SWEW\Test\Exceptions;

use RuntimeException;
use SWEW\Test\Utils\Diff;
use Throwable;

final class ExpectException extends RuntimeException implements Throwable
{
    public function __construct(
        mixed  $expectedValue,
        mixed  $gotValue,
        string $message = '',
    ) {
        $message .= "\n" . Diff::diff($expectedValue, $gotValue) . "\n";

        parent::__construct($message);
    }
}
