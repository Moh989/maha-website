<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secureSetting = env('SESSION_SECURE', 'auto');
        $secure = $secureSetting === 'auto'
            ? (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            : (bool) $secureSetting;
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) env('SESSION_LIFETIME', 7200));
        session_name((string) env('SESSION_NAME', 'almaha_session'));
        session_set_cookie_params([
            'lifetime' => (int) env('SESSION_LIFETIME', 7200),
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
