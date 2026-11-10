<?php

declare(strict_types=1);

use Swew\Test\Suite\Suite;

it('Test 1', function () {
    //     $a = str_repeat("Hello", 1024 * 1024 * 4);
    // d($R, memory_get_usage());
    expect(10)->not()->toBe(1);
});

it('Test 2: with dataset', function (int $num, int $n2 = 3) {
    return str_repeat('Hello', $num * 100000);
})->with([
    1,
    [2, 3],
]);

it('Suite executes static datasets and binds ordinary closures', function () {
    $values = [];
    $suite = new Suite('static', static function (int $value) use (&$values) {
        $values[] = $value;
    });
    $result = $suite->with([1, [2]])->run(memory_get_usage());
    expect($values)->toBe([1, 2]);
    expect($result->isExcepted)->toBeFalse();

    $bound = null;
    $suite = new Suite('bound', function () use (&$bound) {
        $bound = $this;
    });
    $suite->run(memory_get_usage());
    expect($bound)->toBe($suite);
});

it('Suite records exceptions and errors', function (Throwable $error) {
    $suite = new Suite('failure', function () use ($error) {
        throw $error;
    });
    $result = $suite->run(memory_get_usage());
    expect($result->isExcepted)->toBeTrue();
    expect($result->exception)->toBe($error);
})->with([new RuntimeException('exception'), new TypeError('type error'), new Error('error')]);

it('CLI reports failures and respects bail', function (string $code, bool $bail, int $status, string $summary) {
    $directory = sys_get_temp_dir().'/swew-test-'.bin2hex(random_bytes(8));
    mkdir($directory);
    try {
        file_put_contents($directory.'/case.spec.php', '<?php '.$code);
        file_put_contents($directory.'/config.json', json_encode(['test' => [
            'paths' => ['*.spec.php'],
            'bail' => $bail,
            'log' => ['logo' => false, 'clear' => false, 'color' => false],
        ]]));
        $process = proc_open(
            [PHP_BINARY, dirname(__DIR__, 3).'/bin/t', '--dir='.$directory, '--config=config.json'],
            [1 => ['pipe', 'w'], 2 => ['redirect', 1]],
            $pipes,
            $directory
        );
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $exitCode = proc_close($process);
        expect($exitCode, $output)->toBe($status);
        expect($output)->toContain($summary);
    } finally {
        unlink($directory.'/case.spec.php');
        unlink($directory.'/config.json');
        rmdir($directory);
    }
})->with([
    ["it('pass', static fn () => expect(1)->toBe(1));", false, 0, '1 passed'],
    ["it('fail', fn () => expect(1)->toBe(2));", false, 1, '1 excepted'],
    ["it('error', fn () => strlen([])); it('next', fn () => expect(true)->toBeTrue());", false, 1, '1 excepted | 1 passed'],
    ["it('error', fn () => strlen([])); it('next', fn () => expect(true)->toBeTrue());", true, 1, '1 excepted | 0 passed'],
]);

it('Test 3: skip', function () {
    sleep(2);
})->skip();

it('Test 4: todo with empty functions');

it('DIFF: new', function () {
    expect("Привет дивный \n новый")
        ->toBe('Привет Мир дивный');
})->skip();

it('DIFF: new 001', function () {
    expect("Привет дивный \n новый")
        ->not
        ->toBe('Привет Мир дивный');
});

it('DIFF: new 002', function () {
    expect("Привет дивный \n новый")
        ->not
        ->toBe('Привет Мир дивный');
});
