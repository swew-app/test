<?php

declare(strict_types=1);

namespace Swew\Test;

use Swew\Cli\Command;
use Swew\Cli\SwewCommander;
use Swew\Cli\Terminal\Output;
use Swew\Test\CliCommands\LoadConfig;
use Swew\Test\CliCommands\RunTests;
use Swew\Test\CliCommands\SearchFiles;
use Swew\Test\CliCommands\ShowTestResults;
use Swew\Test\Config\Config;
use Swew\Test\LogMaster\Log\LogData;
use Swew\Test\Utils\CliStr;
use Swew\Test\Utils\DataConverter;

class TestMaster extends SwewCommander
{
    public readonly Config $config;

    /** @var array<LogData> $testResults */
    public array $testResults = [];

    public float $testingTime = 0;

    public float $startAt = 0;

    protected array $commands = [
        LoadConfig::class,
        SearchFiles::class,
        RunTests::class,
        ShowTestResults::class,
    ];

    public function __construct(
        array $argvLocal = [],
        private readonly Output $output = new Output(),
    ) {
        global $argv;

        if (count($argvLocal) === 0) {
            $argvLocal = $argv;
        }

        $this->config = new Config();

        $this->startAt = microtime(true);

        set_exception_handler(function ($e) {
            $msg = DataConverter::getParsedException($e, '[ Error outside of tests ]');
            CliStr::vm()->output->writeLn($msg);
        });

        parent::__construct($argvLocal, $output);
    }

    public function run(): void
    {
        if ($this->isNeedHelp()) {
            $this->showHelp();
            exit();
        }

        // RUN

        foreach ($this->commands as $commandClass) {
            /** @var Command $command */
            $command = $this->getCommand($commandClass);

            $result = $command();

            if ($result === -1) {
                // кастомный овтет, когда надо досрочно завершить работу без ошибки
                exit();
            }
            if ($result > 0) {
                // Handle error
                exit($result);
            }
        }
    }

    protected function showHelp(): void
    {
        // TODO: собирать только опции
        $result = [];

        foreach ($this->commands as $commandClass) {
            /** @var Command $class */
            $class = new $commandClass();
            $this->fillCommandArguments($class, []);

            $result[] = $class->getHelpMessage('{options}');
        }

        $this->output->writeLn('╭──────────────────────────────────╮');
        $this->output->writeLn('│<green><b>     swew/test</> help message       │');
        $this->output->writeLn('╰──────────────────────────────────╯');

        $helpMessage = implode("\n", $result);

        $this->output->writeLn($helpMessage);
    }
}
