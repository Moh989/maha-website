<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Upload
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];

    public static function store(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException(__('upload_failed'));
        }
        $max = (int) env('UPLOAD_MAX_MB', 8) * 1024 * 1024;
        if (($file['size'] ?? 0) < 1 || $file['size'] > $max) {
            throw new RuntimeException(__('upload_size'));
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime])) {
            throw new RuntimeException(__('upload_type'));
        }
        if (str_starts_with($mime, 'image/') && @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException(__('upload_type'));
        }
        $extension = self::ALLOWED[$mime];
        $directory = date('Y/m');
        $targetDirectory = base_path('storage/uploads/' . $directory);
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0750, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException(__('upload_failed'));
        }
        $filename = bin2hex(random_bytes(20)) . '.' . $extension;
        $target = $targetDirectory . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException(__('upload_failed'));
        }
        chmod($target, 0640);
        return [
            'path' => $directory . '/' . $filename,
            'mime' => $mime,
            'size' => (int) $file['size'],
            'original_name' => mb_substr((string) preg_replace('/[\x00-\x1F\x7F]/u', '', basename((string) $file['name'])), 0, 240),
        ];
    }
}
