<?php

declare(strict_types=1);

namespace App\Core;

final class Activity
{
    public static function log(string $action, string $entityType, ?int $entityId, array $details = []): void
    {
        $user = Auth::user();
        $statement = Database::connection()->prepare(
            'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details_json, ip_address, created_at) VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip, NOW())'
        );
        $statement->execute([
            'user_id' => $user['id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
