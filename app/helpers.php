<?php

declare(strict_types=1);

use App\Core\Csrf;

function base_path(string $path = ''): string
{
    return dirname(__DIR__) . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return match (strtolower($value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        'empty', '(empty)' => '',
        default => $value,
    };
}

function config(string $key, mixed $default = null): mixed
{
    static $configs = [];
    [$file, $item] = array_pad(explode('.', $key, 2), 2, null);
    if (!isset($configs[$file])) {
        $path = base_path("config/{$file}.php");
        $configs[$file] = is_file($path) ? require $path : [];
    }
    return $item === null ? $configs[$file] : ($configs[$file][$item] ?? $default);
}

function locale(): string
{
    return $_SESSION['locale'] ?? 'ar';
}

function set_locale(string $locale): void
{
    if (!in_array($locale, ['ar', 'en'], true)) {
        $locale = 'ar';
    }
    $_SESSION['locale'] = $locale;
    setcookie('almaha_locale', $locale, [
        'expires' => time() + 31536000,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function __(string $key, array $replace = []): string
{
    static $translations = [];
    $lang = locale();
    if (!isset($translations[$lang])) {
        $file = base_path("app/Lang/{$lang}.php");
        $translations[$lang] = is_file($file) ? require $file : [];
    }
    $text = (string) ($translations[$lang][$key] ?? $key);
    foreach ($replace as $name => $value) {
        $text = str_replace(':' . $name, (string) $value, $text);
    }
    return $text;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return rtrim((string) config('app.url'), '/') . '/' . ltrim($path, '/');
}

function localized_url(string $path = ''): string
{
    $prefix = locale() === 'en' ? '/en' : '';
    return url($prefix . '/' . ltrim($path, '/'));
}

function localized_field(array $row, string $field): string
{
    $key = $field . '_' . locale();
    $fallback = $field . '_' . (locale() === 'ar' ? 'en' : 'ar');
    return (string) ($row[$key] ?? $row[$fallback] ?? '');
}

function is_active_path(string $path): bool
{
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return rtrim($requestPath, '/') === rtrim(parse_url(localized_url($path), PHP_URL_PATH) ?: '/', '/');
}
