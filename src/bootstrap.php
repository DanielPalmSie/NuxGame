<?php

use App\Config\Env;

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

Env::load(__DIR__ . '/../.env');

if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}
