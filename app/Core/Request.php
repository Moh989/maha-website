<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $configuredBase = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');
        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptBase = str_ends_with(strtolower($scriptName), '.php')
            ? rtrim(str_replace('\\', '/', dirname($scriptName)), '/')
            : '';

        foreach ([$configuredBase, $scriptBase] as $base) {
            if (
                $base !== ''
                && $base !== '/'
                && ($path === $base || str_starts_with($path, $base . '/'))
            ) {
                $path = substr($path, strlen($base)) ?: '/';
                break;
            }
        }
        return '/' . ltrim(rawurldecode($path), '/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function ip(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
    }

    public function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }
}
