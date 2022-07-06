<?php

require __DIR__ . '/../../vendor/autoload.php';

use SWEW\Test\LogMaster\LogMaster;
use SWEW\Test\TestRunner;

chdir(__DIR__);

TestRunner::init();

$res = TestRunner::run();

$log = new LogMaster($res);

$log->logListAndExit();
