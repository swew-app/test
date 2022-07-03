<?php

require __DIR__ . '/../../vendor/autoload.php';

use SWEW\Test\Runner\TestManager;

chdir(__DIR__);

TestManager::init();

$res = TestManager::run();

dd($res);


// [ ]: Log Printer
// [ ]: Parallel Runner
