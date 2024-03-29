<?php

declare(strict_types=1);

namespace Swew\Test\Exceptions;

use RuntimeException;
use Throwable;

final class Exception extends RuntimeException implements Throwable
{
    public function __construct(string $message = "", int $code = 0, \Throwable|null $previous = null)
    {
        if (!empty($message)) {
            $red = "\033[31m";
            $off = "\033[0m";
            $message = "\n\n$red $message $off\n\n";
        }

        parent::__construct($message, $code, $previous);
    }
}
