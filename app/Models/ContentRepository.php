<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class ContentRepository
{
    public function page(string $slug): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT p.*, s.title_ar AS seo_title_ar, s.title_en AS seo_title_en, s.description_ar AS seo_description_ar, s.description_en AS seo_description_en, s.og_image
             FROM pages p LEFT JOIN seo_metadata s ON s.entity_type = 'pages' AND s.entity_id = p.id
             WHERE p.slug = :slug AND p.status = 'published' AND p.deleted_at IS NULL LIMIT 1"
        );
        $statement->execute(['slug' => $slug]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function all(string $table, int $limit = 50): array
    {
        $allowed = ['services', 'products', 'projects', 'slider_items', 'news', 'faqs', 'media'];
        if (!in_array($table, $allowed, true)) {
            return [];
        }
        $limit = max(1, min($limit, 100));
        $sql = "SELECT * FROM {$table} WHERE status = 'published'";
        if ($this->hasDeletedAt($table)) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $sql .= " ORDER BY sort_order ASC, id DESC LIMIT {$limit}";
        return Database::connection()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function settings(): array
    {
        $settings = [];
        foreach (Database::connection()->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function navigation(): array
    {
        return Database::connection()->query(
            "SELECT slug, title_ar, title_en FROM pages WHERE status = 'published' AND show_in_nav = 1 AND deleted_at IS NULL ORDER BY sort_order, id"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<string> */
    public function publishedSlugs(): array
    {
        return Database::connection()->query(
            "SELECT slug FROM pages WHERE status = 'published' AND deleted_at IS NULL ORDER BY sort_order, id"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    private function hasDeletedAt(string $table): bool
    {
        return true;
    }
}
