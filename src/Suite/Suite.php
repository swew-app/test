<?php

declare(strict_types=1);

namespace SWEW\Test\Suite;

use Closure;
use SWEW\Test\Suite\Log\LogData;

final class Suite
{
    private array $dateset = [];

    private bool $isSkip = false;

    private ?LogData $logData = null;

    public bool $isOnly = false;

    public function __construct(
        private readonly string  $message,
        private readonly Closure $testCase
    ) {
    }

    public function run(int $memory): LogData
    {
        $this->logData = new LogData($memory, $this->message);

        if ($this->isSkip) {
            $this->logData->isSkip = true;

            return $this->stopLogData();
        }

        try {
            if (count($this->dateset) > 0) {
                foreach ($this->dateset as $args) {
                    if (is_array($args)) {
                        $this->executeTestCase($args);
                    } else {
                        $this->executeTestCase([$args]);
                    }
                }
            } else {
                $this->executeTestCase();
            }
        } catch (\Exception $e) {
            return $this->logData->setException($e);
        }

        return $this->stopLogData();
    }

    public function stopLogData(): LogData
    {
        return $this->logData->stop(memory_get_usage());
    }

    private function executeTestCase(array $params = []): void
    {
        $fn = $this->testCase;
        $fn->bindTo($this)(...$params);
    }


    public function with(array $dateset): self
    {
        $this->dateset = $dateset;

        return $this;
    }

    public function skip(?Closure $closure = null): self
    {
        if (is_null($closure)) {
            $this->isSkip = true;
        } else {
            $this->isSkip = $closure();
        }

        return $this;
    }

    public function only(): self
    {
        $this->isOnly = true;

        return $this;
    }
}
