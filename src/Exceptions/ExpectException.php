<?php

declare(strict_types=1);

namespace SWEW\Test\Exceptions;

use RuntimeException;
use SWEW\Test\Utils\CliStr;
use SWEW\Test\Utils\Diff;
use Throwable;

final class ExpectException extends RuntimeException implements Throwable
{
    public function __construct(
        mixed  $expectedValue,
        mixed  $gotValue,
        string $message = '',
    ) {
        $msg = CliStr::cl('R', " ⚠️  " . str_pad($message, 77, ' '));

        $diff = Diff::diff($expectedValue, $gotValue);

        if ($diff !== '') {
            $msg .= "\n" . $diff . "\n";
        }

        parent::__construct($msg);
    }
}
