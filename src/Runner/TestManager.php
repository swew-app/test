<?php

declare(strict_types=1);

namespace SWEW\Test\Runner;

use SWEW\Test\Exceptions\Exception;
use SWEW\Test\Suite\Suite;

final class TestManager
{
    public static function init()
    {
        $configFile = getcwd() . DIRECTORY_SEPARATOR . 'swew-test.json';

        $json = file_get_contents($configFile);

        if (!$json) {
            throw new Exception("Can't load config file: '{$configFile}'");
        }

        $config = json_decode($json, true);

        self::checkConfigValidation($config);

        self::$queue = [];

        $testFiles = self::loadTestFilePaths($config['paths']);

        foreach ($testFiles as $file) {
            self::loadTestFile($file);
        }
    }

    // TODO: перевести на SplQueue
    private static array $queue = [];

    public static function add(Suite $suite): void
    {
        self::$queue[] = $suite;
    }

    private static ?Suite $currentSuite = null;

    public static function run()
    {
        $results = [];

        $hasOnlyTest = false;

        foreach (self::$queue as $suite) {
            if ($suite->isOnly) {
                $hasOnlyTest = true;
                break;
            }
        }

        $list = $hasOnlyTest ? array_filter(self::$queue, fn ($s) => $s->isOnly) : self::$queue;

        foreach ($list as $suite) {
            self::$currentSuite = $suite;
            $suiteResult = $suite->run(memory_get_usage());
            $results[] = $suiteResult;
            self::$currentSuite = null;
        }

        dd($results);

        return $results;
    }

    public static function getCurrentSuite(): Suite
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
        require_once $file;
    }
}
