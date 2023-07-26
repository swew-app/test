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

        $msg = "\n<bgRed>" .str_pad(" $message ", $width, ' ', STR_PAD_BOTH) . "</>";

        $diff = Diff::diff($expectedValue, $gotValue);

        if ($diff !== '') {
            $msg .= "\n" . $diff . "\n";
        }

        $msg = CliStr::vm()->output->format($msg);

        parent::__construct($msg);
    }
}
