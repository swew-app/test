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

        $msg = "<bgRed>" .str_pad(" $message ", $width, ' ', STR_PAD_BOTH) . "</>";

        $diff = Diff::diff($expectedValue, $gotValue);

        parent::__construct(
            CliStr::vm()->output->format($msg . $diff)
        );
    }
}
