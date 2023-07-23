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

        $width = CliStr::vm()->width();

        $msg = '<bgRed>';
        $msg .= str_pad('', $width, ' ') . "\n";
        $msg .= str_pad(" $message", $width, ' ') . "\n";
        $msg .= str_pad('', $width, ' ') . "</>";

        $diff = Diff::diff($expectedValue, $gotValue);

        if ($diff !== '') {
            $msg .= "\n" . $diff . "\n";
        }

        $msg = CliStr::vm()->output->format($msg);

        parent::__construct($msg);
    }
}
