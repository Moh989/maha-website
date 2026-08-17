<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Upload;
use Throwable;

final class MediaController
{
    public function upload(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_token'))) {
            Session::flash('error', __('csrf_error'));
            Response::redirect('/admin/media');
        }
        $stored = null;
        try {
            $stored = Upload::store($_FILES['file'] ?? []);
            $statement = Database::connection()->prepare(
                "INSERT INTO media (filename, original_name, path, mime_type, file_size, alt_ar, alt_en, status, sort_order, uploaded_by, created_at, updated_at) VALUES (:filename, :original_name, :path, :mime, :size, :alt_ar, :alt_en, 'published', 0, :user_id, NOW(), NOW())"
            );
            $statement->execute([
                'filename' => basename($stored['path']), 'original_name' => $stored['original_name'], 'path' => $stored['path'],
                'mime' => $stored['mime'], 'size' => $stored['size'], 'alt_ar' => mb_substr(trim((string) $request->input('alt_ar')), 0, 255),
                'alt_en' => mb_substr(trim((string) $request->input('alt_en')), 0, 255), 'user_id' => Auth::user()['id'],
            ]);
            Activity::log('create', 'media', (int) Database::connection()->lastInsertId(), ['name' => $stored['original_name']]);
            Session::flash('success', 'تم رفع الملف بنجاح.');
        } catch (Throwable $exception) {
            if (is_array($stored)) {
                $orphan = base_path('storage/uploads/' . $stored['path']);
                if (is_file($orphan)) {
                    unlink($orphan);
                }
            }
            error_log('Upload error: ' . $exception->getMessage());
            Session::flash('error', $exception->getMessage());
        }
        Response::redirect('/admin/media');
    }

    public function serve(Request $request, string $id): void
    {
        Auth::requireLogin();
        $statement = Database::connection()->prepare('SELECT * FROM media WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => (int) $id]);
        $media = $statement->fetch();
        if (!$media) {
            http_response_code(404);
            exit;
        }
        $root = realpath(base_path('storage/uploads')) ?: '';
        $path = realpath(base_path('storage/uploads/' . $media['path'])) ?: '';
        if ($root === '' || $path === '' || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . $media['mime_type']);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="' . rawurlencode($media['original_name']) . '"');
        readfile($path);
        exit;
    }
}
