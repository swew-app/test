<?php

declare(strict_types=1);

namespace Swew\Test\Exceptions;

use RuntimeException;
use Swew\Test\Utils\CliStr;
use Swew\Test\Utils\Diff;
use Throwable;

final class ExpectException extends RuntimeException implements Throwable
{
    public function __construct(
        mixed  $expectedValue,
        mixed  $gotValue,
        string $message = '',
    ) {
        $msg = CliStr::cl('R', str_pad('', 77, ' '));
        $msg .= "\n";
        $msg .= CliStr::cl('Rw', str_pad(" $message", 77, ' '));
        $msg .= "\n";
        $msg .= CliStr::cl('R', str_pad('', 77, ' '));

        $diff = Diff::diff($expectedValue, $gotValue);

        if ($diff !== '') {
            $msg .= "\n" . $diff . "\n";
        }

        parent::__construct($msg);
    }
}
