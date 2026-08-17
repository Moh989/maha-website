<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Auth
{
    private static ?array $user = null;

    public static function attempt(string $email, string $password, Request $request): bool
    {
        $pdo = Database::connection();
        $hash = hash('sha256', strtolower(trim($email)) . '|' . $request->ip());
        $window = date('Y-m-d H:i:s', time() - 900);
        $attempts = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE identity_hash = :hash AND attempted_at >= :window AND successful = 0');
        $attempts->execute(['hash' => $hash, 'window' => $window]);
        if ((int) $attempts->fetchColumn() >= 5) {
            Session::flash('error', __('login_locked'));
            return false;
        }

        $statement = $pdo->prepare('SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = :email AND u.deleted_at IS NULL LIMIT 1');
        $statement->execute(['email' => strtolower(trim($email))]);
        $user = $statement->fetch(PDO::FETCH_ASSOC) ?: null;
        $success = $user && (int) $user['is_active'] === 1 && password_verify($password, $user['password_hash']);

        $log = $pdo->prepare('INSERT INTO login_attempts (identity_hash, ip_address, attempted_at, successful) VALUES (:hash, :ip, NOW(), :successful)');
        $log->execute(['hash' => $hash, 'ip' => $request->ip(), 'successful' => $success ? 1 : 0]);

        if (!$success) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        Csrf::rotate();
        $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute(['id' => $user['id']]);
        self::$user = $user;
        return true;
    }

    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        $statement = Database::connection()->prepare('SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id AND u.is_active = 1 AND u.deleted_at IS NULL');
        $statement->execute(['id' => (int) $_SESSION['user_id']]);
        self::$user = $statement->fetch() ?: null;
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Response::redirect('/admin/login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array((string) self::user()['role_slug'], $roles, true)) {
            http_response_code(403);
            View::render('pages/403', ['pageTitle' => __('forbidden')]);
            exit;
        }
    }

    public static function logout(): void
    {
        self::$user = null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], true);
        }
        session_destroy();
    }
}
