<?php

require __DIR__ . '/../../vendor/autoload.php';

use Swew\Test\LogMaster\LogMaster;
use Swew\Test\TestRunner;

chdir(__DIR__);

TestRunner::init();

$res = TestRunner::run();

$log = new LogMaster($res);

$log->logListAndExit();
