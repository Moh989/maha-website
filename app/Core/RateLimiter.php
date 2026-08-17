<?php

declare(strict_types=1);

namespace App\Core;

final class RateLimiter
{
    public static function tooMany(string $action, string $ip, int $max = 5, int $minutes = 30): bool
    {
        $pdo = Database::connection();
        $key = hash('sha256', $action . '|' . $ip);
        $window = date('Y-m-d H:i:s', time() - ($minutes * 60));
        $statement = $pdo->prepare('SELECT COUNT(*) FROM rate_limits WHERE action_key = :key AND created_at >= :window');
        $statement->execute(['key' => $key, 'window' => $window]);
        return (int) $statement->fetchColumn() >= $max;
    }

    public static function hit(string $action, string $ip): void
    {
        $statement = Database::connection()->prepare('INSERT INTO rate_limits (action_key, created_at) VALUES (:key, NOW())');
        $statement->execute(['key' => hash('sha256', $action . '|' . $ip)]);
    }
}
