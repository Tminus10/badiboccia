<?php

spl_autoload_register(function (string $class): void {
    $dirs = [
        __DIR__,
        __DIR__ . '/Models',
        __DIR__ . '/Services',
        __DIR__ . '/Auth',
        __DIR__ . '/Controllers',
    ];
    foreach ($dirs as $dir) {
        $path = "$dir/$class.php";
        if (is_file($path)) {
            require $path;
            return;
        }
    }
});
