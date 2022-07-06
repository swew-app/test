<?php

declare(strict_types=1);

namespace SWEW\Test;

use Closure;
use SWEW\Test\Exceptions\Exception;
use SWEW\Test\LogMaster\Log\LogState;
use SWEW\Test\Suite\Suite;
use SWEW\Test\Suite\SuiteGroup;
use SWEW\Test\Suite\SuiteHook;

final class TestRunner
{
    private static array $suiteGroupList = [];

    private static SuiteGroup $currentSuiteGroup;

    public static float $testingTime = 0;

    private static array $config = [];

    public static function init(): void
    {
        $configFile = getcwd() . DIRECTORY_SEPARATOR . 'swew-test.json';

        $json = file_get_contents($configFile);

        if (!$json) {
            throw new Exception("Can't load config file: '{$configFile}'");
        }

        self::$config = json_decode($json, true);

        self::checkConfigValidation(self::$config);

        self::$suiteGroupList = [];

        $testFiles = self::loadTestFilePaths(self::$config['paths']);

        foreach ($testFiles as $file) {
            self::loadTestFile($file);
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
        clear_cli();

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

        // Run tests
        foreach ($list as $suiteGroup) {
            $testsCount = $suiteGroup->getTestsCount();

            $suiteGroup->run(
                $results,
                $hasOnlyFilteredTests,
                fn (Suite|null $suite) => TestRunner::setCurrentSuite($suite)
            );
        }

        self::$testingTime = microtime(true) - $startTime;

        $log = new LogState();

        $log->setResults($results);
        $log->setTestingTime(self::$testingTime);
        $log->setHasOnlyTests($hasOnlyFilteredTests);
        $log->setTestsCount($testsCount);
        $log->setRootDir(get_project_root() ?: '');
        $log->setConfig(self::$config);

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

    private static function checkConfigValidation(array $config): void
    {
        if (empty($config['paths'])) {
            throw new Exception("'paths' - is required parameter in config file.");
        }
    }

    private static function loadTestFilePaths(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            $files = array_merge($files, glob($path));
        }

        return $files;
    }

    private static function loadTestFile(string $file): void
    {
        self::$currentSuiteGroup = new SuiteGroup($file);

        require_once $file;

        self::$suiteGroupList[] = self::$currentSuiteGroup;
    }
}
