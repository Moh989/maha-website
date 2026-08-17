<?php
$assetSuffix = env('APP_ENV', 'production') === 'production' ? '.min' : '';
$adminStylesheet = '/assets/css/admin' . $assetSuffix . '.css';
$adminStylesheetVersion = (string) (@filemtime(base_path('public' . $adminStylesheet)) ?: 1);
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($pageTitle ?? 'معاينة') ?></title><meta name="robots" content="noindex,nofollow"><link rel="stylesheet" href="<?= e(url($adminStylesheet . '?v=' . $adminStylesheetVersion)) ?>"></head><body class="preview-body"><?= $content ?></body></html>
