<?php

declare(strict_types=1);

namespace SWEW\Test\Suite\Log;

use Exception;

final class LogData
{
    private int $memoryBefore = 0;

    public int $memoryUsage = 0;

    private float $timeBefore = 0;

    public float $timeUsage = 0;

    public bool $isSkip = false;

    public bool $isTodo = false;

    public bool $isOnly = false;

    public bool $isExcepted = false;

    public ?Exception $exception = null;

    public function __construct(
        int $memoryBefore,
        public string $message = ''
    ) {
        $this->memoryBefore = $memoryBefore;
        $this->timeBefore = microtime(true);
    }

    public function stop(int $memory): self
    {
        if (!$this->memoryUsage) {
            $this->memoryUsage = $memory - $this->memoryBefore;
            $this->timeUsage = microtime(true) - $this->timeBefore;
        }

        return $this;
    }

    public function setException(Exception $exception): self
    {
        $this->exception = $exception;
        $this->isExcepted = true;
        return $this->stop(memory_get_usage());
    }
}
