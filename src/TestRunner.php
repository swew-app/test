<?php

declare(strict_types=1);

namespace SWEW\Test;

use Closure;
use SWEW\Test\Exceptions\Exception;
use SWEW\Test\LogMaster\Log\LogState;
use SWEW\Test\Suite\Suite;
use SWEW\Test\Suite\SuiteGroup;
use SWEW\Test\Suite\SuiteHook;
use SWEW\Test\Utils\CliStr;

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

        $paths = self::makeSubPathPatterns(self::$config['paths']);

        $testFiles = self::loadTestFilePaths($paths);

        foreach ($testFiles as $file) {
            self::loadTestFile($file);
        }

        self::clearCli();

        self::showLogo();
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

        // Run tests
        /** @var SuiteGroup $suiteGroup */
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
        $log->setRootPath(self::getRootPath());
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

    private static function makeSubPathPatterns(array $paths): array
    {
        $added = [];

        foreach ($paths as $path) {
            if (str_contains($path, '**')) {
                $added[] = str_replace('**/', '', $path);
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
        if (self::$config['log'] && self::$config['log']['clear'] === false) {
            return;
        }

        CliStr::clear();
    }

    private static function showLogo(): void
    {
        if (self::$config['log'] && self::$config['log']['logo'] === false) {
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

    private static function getRootPath(): string
    {
        $dirs = explode(DIRECTORY_SEPARATOR, __DIR__);

        $i = count($dirs) + 1;

        while ($i--) {
            array_splice($dirs, $i);
            $path = implode(
                DIRECTORY_SEPARATOR,
                $dirs
            );

            if ($path === '') {
                break;
            }

            if (file_exists($path . DIRECTORY_SEPARATOR . 'composer.json')) {
                return $path;
            }
        }

        return '';
    }
}
