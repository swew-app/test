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

final class TestRunner
{
    private static array $suiteGroupList = [];

    private static SuiteGroup $currentSuiteGroup;

    public static float $testingTime = 0;

    public static function init(): void
    {
        self::cliInit();

        self::cliPreload();

        ConfigMaster::loadConfig();

        self::cliUpdateConfig();

        $paths = self::makeSubPathPatterns((array) ConfigMaster::getConfig('paths'));

        $testFiles = self::loadTestFilePaths($paths);

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
            'file,f' => [
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
        ]);
    }

    public static function cliPreload(): void
    {
        if (CliArgs::hasArg('help')) {
            CliStr::write(CliArgs::getHelp());

            exit(0);
        }

        if (CliArgs::hasArg('init')) {
            $configFile = ConfigMaster::createConfigFile();
            CliStr::write([
                CliStr::cl('cyan', 'Created new config file:'),
                ' ' . $configFile,
                ''
            ]);

            exit(0);
        }
    }

    private static function cliUpdateConfig(): void
    {
        if (CliArgs::hasArg('no-color')) {
            ConfigMaster::setConfig('log.color', false);
            CliStr::withColor(false);
        }

        if (CliArgs::hasArg('short')) {
            ConfigMaster::setConfig('log.short', true);
        }

        $filePattern = CliArgs::getGlobMaskPattern('file');

        if (!is_null($filePattern)) {
            ConfigMaster::setConfig('paths', [$filePattern]);
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

            $suiteGroup->run(
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

    private static function makeSubPathPatterns(array $paths): array
    {
        $added = [];

        foreach ($paths as $path) {
            if (str_contains($path, '**')) {
                $added[] = str_replace('**', '', $path);
                $added[] = str_replace('**', '*', $path);
                $added[] = str_replace('**', '*/*', $path);
                $added[] = str_replace('**', '*/*/*', $path);
                $added[] = str_replace('**', '*/*/*/*', $path);
                $added[] = str_replace('**', '*/*/*/*/*', $path);
                $added[] = str_replace('**', '*/*/*/*/*/*', $path);
            }
        }

        return array_merge($paths, $added);
    }

    private static function loadTestFilePaths(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            $files = array_merge($files, glob($path, GLOB_ERR));
        }

        // filter vendor
        $files = array_filter($files, function (string $path) {
            return !str_contains($path, 'vendor');
        });

        return array_unique($files);
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

        CliStr::clear();
    }

    private static function showLogo(): void
    {
        if (ConfigMaster::getConfig('log.logo') === false) {
            return;
        }

        $logo = [
            '',
            '       __   _       ____  _',
            '      ( (` \ \    /| |_  \ \    /',
            '      _)_)  \_\/\/ |_|__  \_\/\/',
            '            .-. .-. .-. .-.',
            '             |  |-  `-.  |',
            '             \'  `-\' `-\'  \'',
            '',
        ];

        CliStr::write($logo);
    }
}
