<?php

declare(strict_types=1);

namespace Swew\Test\Suite;

use Closure;
use Swew\Test\LogMaster\Log\LogData;
use Swew\Test\Utils\CliStr;
use Swew\Test\Utils\DataConverter;

final class Suite
{
    private array $dateset = [];

    private bool $isSkip = false;

    private bool $isTodo = false;

    public bool $isOnly = false;

    public bool $isExcepted = false;

    public string $testFilePath = '';

    private ?LogData $logData = null;

    public function __construct(
        public readonly string  $message,
        private readonly Closure $testCase
    ) {
    }

    public function run(int $memory): LogData
    {
        $this->logData = new LogData($memory, $this->message);

        $this->logData->testFilePath = $this->testFilePath;

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
            $this->isExcepted = true;

            return $this->logData->setException($e);
        }

        return $this->stopLogData();
    }

    public function stopLogData(): LogData
    {
        if (is_null($this->logData)) {
            throw new \LogicException('Empty LogData');
        }

        $log = $this->logData->stop(memory_get_usage());

        //        CliStr::vm()->write(DataConverter::getIcon($log));

        return  $log;
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
