<?php

require __DIR__ . '/../../vendor/autoload.php';

use SWEW\Test\LogMaster\LogMaster;
use SWEW\Test\Runner\TestManager;

chdir(__DIR__);

TestManager::init();

$res = TestManager::run();

$log = new LogMaster($res, [], TestManager::$testingTime);

$log->logList();
