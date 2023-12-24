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
use Swew\Test\LogMaster\Log\LogData;

class TestMaster extends SwewCommander
{
    public array $config = [
        'preloadFile' => '',
        'paths' => ['*.spec.php', '*.test.php'],
        'bail' => false,
        'log' => [
            'logo' => true,
            'color' => true,
            'traceReverse' => true,
            'clear' => true,
            'short' => false,
        ],
        '_filter' => '',
        '_suite' => '',
        '_root' => '',
        // Будем хранить список файлов, после можем очистить
        '_testFiles' => [],
    ];

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
        array                   $argvLocal = [],
        private readonly Output $output = new Output(),
    )
    {
        global $argv;

        if (count($argvLocal) === 0) {
            $argvLocal = $argv;
        }

        $this->startAt = microtime(true);

        parent::__construct($argvLocal, $output);

        // TODO: Запускаем, если выставлен bail:true то останавливаем при первой ошибке
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

            $command();
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
