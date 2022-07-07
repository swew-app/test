<?php

declare(strict_types=1);

if (!function_exists('__dump')) {
    function __dump(): void
    {
        $str = '';
        try {
            foreach (func_get_args() as $x) {
                $str = $str . "\n" . var_export($x, true);
            }
        } catch (Exception) {
            ob_start();
            foreach (func_get_args() as $x) {
                echo "\n";
                var_dump($x);
            }
            $str = ob_get_clean();
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $line = $backtrace[1]['line'];
        $file = $backtrace[1]['file'];

        if (defined('STDIN')) {
            echo "\n 🔍\033[0;33m ⮕ \033[0;36m$file\033[90m:$line\033[0m\n";
            echo $str . "\n";
            return;
        }

        $str = preg_replace(
            "/\[([^\]]+)\] =>/",
            '<b style="color:#888">[</b><span style="color:#17661c">$1</span><b style="color:#888">]</b> <span style="color:#999">=></span>',
            $str
        );
        echo "<pre> 🔍 >>> $file:$line \n";
        echo $str . "</pre>";
    }
}

if (!function_exists('d')) {
    function d(): void
    {
        $args = func_get_args();
        __dump(...$args);
    }
}

if (!function_exists('dd')) {
    function dd(): void
    {
        $args = func_get_args();
        __dump(...$args);
        die(0);
    }
}
