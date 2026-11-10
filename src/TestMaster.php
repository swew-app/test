<?php

declare(strict_types=1);

namespace Swew\Test;

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
use Swew\Test\Utils\Hash;

class TestMaster extends SwewCommander
{
    public readonly Config $config;

    /** @var array<LogData> */
    public array $testResults = [];

    public float $testingTime = 0;

    public float $startAt = 0;

    public static string $hashWatchFiles = '';

    private bool $isWatchMode = false;

    /** @var array<class-string> */
    protected array $commands = [
        LoadConfig::class,
        SearchFiles::class,
        RunTests::class,
        ShowTestResults::class
    ];

    /** @param list<string> $argvLocal */
    public function __construct(
        array $argvLocal = [],
        private readonly Output $output = new Output()
    ) {
        global $argv;

        $autoloadPath = $GLOBALS['_composer_autoload_path'] ?? null;
        if (is_string($autoloadPath)) {
            require_once $autoloadPath;
        }

        if (count($argvLocal) === 0) {
            $argvLocal = $argv ?? [];
        }

        if (empty(getenv('__TEST__'))) {
            putenv('__TEST__=true');
        }

        $this->config = new Config();

        $this->startAt = microtime(true);

        set_exception_handler(function (\Throwable $e) {
            $msg = DataConverter::getParsedException($e, '[ Error outside of tests ]');
            CliStr::vm()->output->writeLn($msg);
            exit(1);
        });

        parent::__construct($argvLocal, $output);
    }

    /** @param list<string> $arg */
    public static function runTest(array $arg = []): void
    {
        global $argv;

        $argList = count($arg) > 0 ? $arg : $argv ?? [];

        $isWatchMode = in_array('--watch', $argList, true) || in_array('-w', $argList, true);

        if ($isWatchMode === false) {
            ( new self($argList) )->run();
        } else {
            // WATCH MODE

            $pid = \pcntl_fork();

            if ($pid === -1) {
                // Ошибка при создании дочернего процесса
                throw new \RuntimeException('Fork failed');
            } elseif ($pid) {
                // Родительский процесс
                // Ждем завершения дочернего процесса
                $status = 0;
                pcntl_wait($status);

                if ($status > 0) {
                    $exitCode = pcntl_wifexited($status) ? pcntl_wexitstatus($status) : false;
                    exit($exitCode === false ? 1 : $exitCode);
                }

                sleep(1);
                self::runTest($argList);
            } else {
                // Дочерний процесс
                $master = new self($argList);
                $master->isWatchMode = true;
                $master->run();

                self::$hashWatchFiles = Hash::fromFiles($master->config->getTestFiles());

                while (true) {
                    sleep(1);

                    if (self::$hashWatchFiles !== Hash::fromFiles($master->config->getTestFiles())) {
                        break;
                    }
                }
            }
        }
    }

    #[\Override]
    public function run(): void
    {
        if ($this->isNeedHelp()) {
            $this->showHelp();
            exit();
        }

        // RUN

        foreach ($this->commands as $commandClass) {
            $result = $this->call($commandClass, $this->argList);

            if ($result === -1) {
                // кастомный ответ, когда надо досрочно завершить работу без ошибки
                exit();
            }
            if ($result > 0 && !( $this->isWatchMode && $commandClass === ShowTestResults::class )) {
                // Handle error
                exit($result);
            }
        }
    }

    #[\Override]
    protected function showHelp(): void
    {
        // TODO: собирать только опции
        $result = [];

        foreach ($this->commands as $commandClass) {
            $result[] = $this->getCommandHelpMessage($commandClass, '{options}');
        }

        $this->output->writeLn('╭──────────────────────────────────╮');
        $this->output->writeLn('│<green><b>     swew/test</> help message       │');
        $this->output->writeLn('╰──────────────────────────────────╯');

        $helpMessage = implode("\n", $result);

        $this->output->writeLn($helpMessage);
    }
}
