<?php

declare(strict_types=1);

namespace SWEW\Test\Exceptions;

use RuntimeException;
use Throwable;

final class Exception extends RuntimeException implements Throwable
{
    public function __construct(string $message = "", int $code = 0, \Throwable|null $previous = null)
    {
        if (!empty($message)) {
//            $red = "\033[31m";
//            $off = "\033[0m";
            $message = "\n\n $message \n\n";
        }

        parent::__construct($message, $code, $previous);
    }
}
