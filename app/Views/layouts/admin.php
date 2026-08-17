<?php
$assetSuffix = env('APP_ENV', 'production') === 'production' ? '.min' : '';
$adminPath = (new \App\Core\Request())->path();
$adminStylesheet = '/assets/css/admin' . $assetSuffix . '.css';
$adminStylesheetVersion = (string) (@filemtime(base_path('public' . $adminStylesheet)) ?: 1);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'لوحة التحكم') ?> | إدارة شركة المها</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="stylesheet" href="<?= e(url($adminStylesheet . '?v=' . $adminStylesheetVersion)) ?>">
</head>
<body class="admin-body">
<aside class="admin-sidebar" data-admin-sidebar>
    <a class="admin-brand" href="<?= e(url('/admin')) ?>"><span><img src="<?= e(url('/assets/images/logo-maha.png')) ?>" width="254" height="362" alt=""></span><strong>إدارة شركة المها<small>AL-MAHA CMS</small></strong></a>
    <nav>
        <a class="<?= $adminPath === '/admin' ? 'active' : '' ?>" href="<?= e(url('/admin')) ?>"><span>⌂</span>نظرة عامة</a>
        <?php
        $icons = ['pages' => '▤', 'services' => '◇', 'products' => '◫', 'projects' => '◆', 'slider_items' => '▥', 'news' => '◧', 'faqs' => '?', 'media' => '▧', 'contact_messages' => '✉', 'settings' => '⚙', 'users' => '◎', 'activity_logs' => '≡'];
        foreach ($resources as $slug => $definition):
            if (($definition['admin_only'] ?? false) && ($user['role_slug'] ?? '') !== 'administrator') continue;
            $active = str_starts_with($adminPath, '/admin/' . $slug);
        ?>
            <a class="<?= $active ? 'active' : '' ?>" href="<?= e(url('/admin/' . $slug)) ?>"><span><?= e($icons[$slug] ?? '•') ?></span><?= e($definition['label']) ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-bottom"><a href="<?= e(url('/')) ?>" target="_blank" rel="noopener">عرض الموقع ↗</a><form method="post" action="<?= e(url('/admin/logout')) ?>"><?= csrf_field() ?><button type="submit">تسجيل الخروج</button></form></div>
</aside>
<div class="admin-shell">
    <header class="admin-header"><button class="admin-menu-button" type="button" data-admin-menu aria-label="القائمة">☰</button><div><p>لوحة التحكم</p><h1><?= e($pageTitle ?? '') ?></h1></div><div class="admin-user"><span><?= e(mb_substr($user['name'] ?? 'م', 0, 1)) ?></span><div><strong><?= e($user['name'] ?? '') ?></strong><small><?= ($user['role_slug'] ?? '') === 'administrator' ? 'مدير عام' : 'محرر محتوى' ?></small></div></div></header>
    <main class="admin-main">
        <?php if (!empty($flashSuccess)): ?><div class="admin-alert success" role="status"><?= e($flashSuccess) ?></div><?php endif; ?>
        <?php if (!empty($flashError)): ?><div class="admin-alert error" role="alert"><?= e($flashError) ?></div><?php endif; ?>
        <?= $content ?>
    </main>
</div>
<script src="<?= e(url('/assets/js/admin' . $assetSuffix . '.js')) ?>" defer></script>
</body>
</html>
