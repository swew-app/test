<?php

require __DIR__ . '/../../vendor/autoload.php';

use SWEW\Test\Runner\LogMaster\LogMaster;
use SWEW\Test\Runner\TestManager;

chdir(__DIR__);

TestManager::init();

$res = TestManager::run();

$log = new LogMaster($res);

$log->logListAndExit();
