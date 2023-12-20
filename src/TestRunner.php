<?php

declare(strict_types=1);

namespace Swew\Test;

use Closure;
use Swew\Test\LogMaster\Log\LogState;
use Swew\Test\Suite\Suite;
use Swew\Test\Suite\SuiteGroup;
use Swew\Test\Suite\SuiteHook;
use Swew\Test\Utils\CliArgs;
use Swew\Test\Utils\CliStr;
use Swew\Test\Utils\ConfigMaster;
use Swew\Test\Utils\DataConverter;
use Swew\Test\Utils\FileSearcher;

final class TestRunner
{
    private static array $suiteGroupList = [];

    private static SuiteGroup $currentSuiteGroup;

    public static float $testingTime = 0;

    public static function init(): void
    {
        self::cliInit();

        self::cliPreload();

        $loadConfigError = ConfigMaster::loadConfig();

        if ($loadConfigError) {
            CliStr::vm()->write($loadConfigError);
            die();
        }

        self::cliUpdateConfig();

        $paths = FileSearcher::glob(
            (array)ConfigMaster::getConfig('paths'),
            ConfigMaster::getRootPath()
        );

        $filter = CliArgs::val('filter') ?: '';

        $testFiles = FileSearcher::getTestFilePaths($paths, $filter);

        self::$suiteGroupList = [];

        if (ConfigMaster::getConfig('preloadFile')) {
            $preloadFile = ConfigMaster::getConfig('preloadFile');

            if (is_string($preloadFile)) {
                self::loadTestFile($preloadFile);
            }
        }

        foreach ($testFiles as $file) {
            self::loadTestFile($file);
        }

        self::clearCli();

        self::showLogo();
    }

    public static function cliInit(): void
    {
        CliArgs::init([], [
            'init' => [
                'desc' => 'Create a new config file',
            ],
            'filter,f' => [
                'desc' => 'Filter files',
            ],
            'config,c' => [
                'desc' => 'Path to config file',
            ],
            'suite,sf' => [
                'desc' => 'Filter by suite message',
            ],
            'no-color' => [
                'desc' => 'Turn off colors',
            ],
            'short,s' => [
                'desc' => 'Do not show test names and statistics',
            ],
            'dir' => [
                'desc' => 'Base directory to scan for the test files',
            ],
        ]);
    }

    public static function cliPreload(): void
    {
        if (CliArgs::hasArg('help')) {
            CliStr::vm()->write(CliArgs::getHelp());
            exit(0);
        }

        if (CliArgs::hasArg('init')) {
            $result = [];

            $configFile = ConfigMaster::createConfigFile();

            $result[] = '<cyan>Created new config file:</>';
            $result[] = '<bgGreen> </> <green>' . $configFile . '</>' . PHP_EOL . PHP_EOL;

            $answer = CliStr::vm()->output->ask(' <yellow>Add a "test" script to composer.json to run tests?</> [Y/n]');
            $answer = strtolower($answer) ?? 'y';

            if ($answer === 'y') {
                $isScriptAdded = ConfigMaster::addTestScriptToComposer();


                if ($isScriptAdded) {
                    $result[] = ' <cyan> "test" added</>';
                    $result[] = ' <green> Now you can run the tests with the "composer test" command</>' . PHP_EOL;
                }
            }

            CliStr::vm()->write($result);

            exit(0);
        }
    }

    private static function cliUpdateConfig(): void
    {
        if (CliArgs::hasArg('no-color')) {
            ConfigMaster::setConfig('log.color', false);
            CliStr::vm()->withColor(false);
        }

        if (CliArgs::hasArg('short')) {
            ConfigMaster::setConfig('log.short', true);
        }
    }

    public static function add(Suite $suite): void
    {
        self::$currentSuiteGroup->addSuite($suite);
    }

    public static function addHook(SuiteHook $hook, Closure $hookFunction): void
    {
        self::$currentSuiteGroup->addHook($hook, $hookFunction);
    }

    public static function run(): LogState
    {
        if (empty(getenv('__TEST__'))) {
            putenv('__TEST__=true');
        }

        set_exception_handler(function ($e) {
            self::customGlobalErrorHandler($e);
        });

        return self::runTests();
    }

    private static function runTests(): LogState
    {
        $results = [];

        $testsCount = 0;

        $startTime = microtime(true);

        $list = self::$suiteGroupList;

        $hasOnlyFilteredTests = false;

        foreach ($list as $suiteGroup) {
            $hasOnlyFilteredTests = $suiteGroup->hasOnly();

            if ($hasOnlyFilteredTests) {
                break;
            }
        }

        $filterSuiteByMsg = null;

        if (CliArgs::hasArg('suite')) {
            $filterSuiteByMsg = (string)CliArgs::val('suite');
        }

        // Run tests
        /** @var SuiteGroup $suiteGroup */
        foreach ($list as $suiteGroup) {
            $testsCount += $suiteGroup->getTestsCount();

            $suiteGroup->runSuiteTests(
                $results,
                $hasOnlyFilteredTests,
                fn (Suite|null $suite) => TestRunner::setCurrentSuite($suite),
                $filterSuiteByMsg
            );
        }

        self::$testingTime = microtime(true) - $startTime;

        $log = new LogState();

        $log->setResults($results);
        $log->setTestingTime(self::$testingTime);
        $log->setHasOnlyTests($hasOnlyFilteredTests);
        $log->setTestsCount($testsCount);
        $log->setRootPath(ConfigMaster::getRootPath());
        $log->setFilterSuiteByMsg($filterSuiteByMsg);

        return $log;
    }

    private static ?Suite $currentSuite = null;

    public static function setCurrentSuite(Suite|null $suite): void
    {
        self::$currentSuite = $suite;
    }

    public static function getCurrentSuite(): Suite|null
    {
        return self::$currentSuite;
    }

    private static function loadTestFile(string $file): void
    {
        self::$currentSuiteGroup = new SuiteGroup($file);

        require_once $file;

        self::$suiteGroupList[] = self::$currentSuiteGroup;
    }

    public static function clearCli(): void
    {
        if (ConfigMaster::getConfig('log.clear') === false) {
            return;
        }

        CliStr::vm()->clear();
    }

    private static function showLogo(): void
    {
        if (ConfigMaster::getConfig('log.logo') === false) {
            return;
        }

        $version = \Composer\InstalledVersions::getVersion('swew/test');


        /** @var array<string> $logo */
        $logo = [
            '<green>',
            ' __   _       ____  _      ',
            '( (` \ \    /| |_  \ \    /',
            '_)_)  \_\/\/ |_|__  \_\/\/ ',
            '      .-. .-. .-. .-.      ',
            '       |  |-  `-.  |       ',
            '     \'  `-\' `-\'  \'     ',
            $version,
            '</>',
        ];

        $width = CliStr::vm()->width();

        foreach ($logo as &$v) {
            $v = str_pad($v, $width, ' ', STR_PAD_BOTH);
        }

        CliStr::vm()->write($logo);
    }

    private static function customGlobalErrorHandler(\Throwable $e): void
    {
        $msg = DataConverter::getParsedException($e, '[ Error outside of tests ]');

        CliStr::vm()->write($msg);
    }
}
