<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

define('APP_START', microtime(true));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $path = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require dirname(__DIR__) . '/app/helpers.php';
Env::load(dirname(__DIR__) . '/.env');

date_default_timezone_set((string) env('APP_TIMEZONE', 'Asia/Baghdad'));
$debug = (bool) env('APP_DEBUG', false);
Logger::boot(dirname(__DIR__) . '/storage/logs/error_log', $debug);

Session::start();
if (!isset($_SESSION['locale'])) {
    set_locale(in_array($_COOKIE['almaha_locale'] ?? '', ['ar', 'en'], true) ? $_COOKIE['almaha_locale'] : 'ar');
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self'; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';
$router->dispatch(new Request());
