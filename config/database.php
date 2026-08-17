<?php

declare(strict_types=1);

return [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_NAME', 'almaha_website'),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASS', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
];
