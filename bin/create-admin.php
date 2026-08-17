<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;
use App\Core\Logger;

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $path = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) require $path;
});
require dirname(__DIR__) . '/app/helpers.php';
Env::load(dirname(__DIR__) . '/.env');
date_default_timezone_set((string) env('APP_TIMEZONE', 'Asia/Baghdad'));
Logger::boot(dirname(__DIR__) . '/storage/logs/error_log', (bool) env('APP_DEBUG', false));

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

$email = strtolower(trim((string) ($argv[1] ?? '')));
$name = trim((string) ($argv[2] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
    fwrite(STDERR, "Usage: php bin/create-admin.php admin@example.com \"Administrator Name\"\n");
    exit(1);
}

fwrite(STDOUT, "Password (minimum 12 characters): ");
if (function_exists('shell_exec')) shell_exec('stty -echo');
$password = trim((string) fgets(STDIN));
if (function_exists('shell_exec')) shell_exec('stty echo');
fwrite(STDOUT, "\n");
if (strlen($password) < 12) {
    fwrite(STDERR, "Password must contain at least 12 characters.\n");
    exit(1);
}

$pdo = Database::connection();
$roleId = $pdo->query("SELECT id FROM roles WHERE slug = 'administrator' LIMIT 1")->fetchColumn();
if (!$roleId) {
    fwrite(STDERR, "Administrator role is missing. Import database/seed.sql first.\n");
    exit(1);
}
$statement = $pdo->prepare('INSERT INTO users (role_id, name, email, password_hash, is_active, created_at, updated_at) VALUES (:role, :name, :email, :password, 1, NOW(), NOW())');
$statement->execute(['role' => $roleId, 'name' => $name, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
fwrite(STDOUT, "Administrator account created successfully.\n");
