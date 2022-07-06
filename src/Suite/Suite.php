<?php

declare(strict_types=1);

namespace SWEW\Test\Suite;

use Closure;
use SWEW\Test\Runner\LogMaster\Log\LogData;

final class Suite
{
    private array $dateset = [];

    private bool $isSkip = false;

    private bool $isTodo = false;

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

        if ($this->isOnly) {
            $this->logData->isOnly = true;
        }

        if ($this->isSkip) {
            $this->logData->isSkip = true;

            return $this->stopLogData();
        }

        if ($this->isTodo) {
            $this->logData->isTodo = true;

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
        if (is_null($this->logData)) {
            throw new \Exception('Empty LogData');
        }

        return $this->logData->stop(memory_get_usage());
    }

    private function executeTestCase(array $params = []): void
    {
        $fn = $this->testCase;
        $boundFn = $fn->bindTo($this);

        if (!empty($boundFn)) {
            $boundFn(...$params);
        }
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

    public function todo(): self
    {
        $this->isTodo = true;

        return $this;
    }

    public function only(): self
    {
        $this->isOnly = true;

        return $this;
    }
}
