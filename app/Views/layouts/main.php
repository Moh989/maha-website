<?php
$title = $pageTitle ?? (locale() === 'ar' ? config('app.name_ar') : config('app.name_en'));
$description = $pageDescription ?? '';
$canonicalPath = (new \App\Core\Request())->path();
$canonical = url($canonicalPath);
$languageNeutralPath = preg_replace('#^/en(?=/|$)#', '', $canonicalPath) ?: '/';
$arabicHref = url($languageNeutralPath);
$englishHref = url('/en' . ($languageNeutralPath === '/' ? '' : $languageNeutralPath));
$alternatePath = locale() === 'ar'
    ? '/en' . ($languageNeutralPath === '/' ? '' : $languageNeutralPath)
    : $languageNeutralPath;
$ogImage = url($page['og_image'] ?? '/assets/images/refinery-landscape.jpg');
$publishedSlugs = $publishedSlugs ?? [];
$isPublished = static fn (string $slug): bool => in_array($slug, $publishedSlugs, true);
$aboutSlugs = ['about', 'vision-mission', 'capabilities', 'sectors'];
$hasPublishedAbout = (bool) array_intersect($aboutSlugs, $publishedSlugs);
$isAboutActive = is_active_path('/about') || is_active_path('/vision-mission') || is_active_path('/capabilities') || is_active_path('/sectors');
$serviceSlugs = ['business-lines', 'oil-services', 'oxidized-asphalt', 'lubricating-oils', 'petroleum-supplies', 'general-services'];
$hasPublishedService = (bool) array_intersect($serviceSlugs, $publishedSlugs);
$assetSuffix = env('APP_ENV', 'production') === 'production' ? '.min' : '';
$siteStylesheet = '/assets/css/site' . $assetSuffix . '.css';
$siteStylesheetVersion = (string) (@filemtime(base_path('public' . $siteStylesheet)) ?: 1);
?>
<!doctype html>
<html lang="<?= e(locale()) ?>" dir="<?= locale() === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="theme-color" content="#07192c">
    <meta name="color-scheme" content="light dark">
    <title><?= e($title) ?></title>
    <link rel="canonical" href="<?= e($canonical) ?>">
    <link rel="alternate" hreflang="ar" href="<?= e($arabicHref) ?>">
    <link rel="alternate" hreflang="en" href="<?= e($englishHref) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= e($arabicHref) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= locale() === 'ar' ? 'ar_IQ' : 'en_US' ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($title) ?>">
    <meta name="twitter:description" content="<?= e($description) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">
    <script>(function(){try{var t=localStorage.getItem('almaha-theme');document.documentElement.dataset.theme=t||(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light')}catch(e){}})();</script>
    <link rel="preload" href="<?= e(url($siteStylesheet . '?v=' . $siteStylesheetVersion)) ?>" as="style">
    <link rel="stylesheet" href="<?= e(url($siteStylesheet . '?v=' . $siteStylesheetVersion)) ?>">
    <link rel="icon" type="image/png" href="<?= e(url('/assets/images/logo-maha.png')) ?>">
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org', '@type' => 'Organization',
        'name' => config('app.name_en'), 'alternateName' => config('app.name_ar'),
        'url' => url('/'), 'logo' => url('/assets/images/logo-maha.png'),
        'foundingDate' => '2016-06', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Baghdad', 'addressRegion' => 'Baghdad', 'addressCountry' => 'IQ'],
        'email' => $settings['contact_email'] ?? 'info@almaha-group.com',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php if (!empty($page['slug'])): ?><script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => __('home'), 'item' => localized_url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => localized_field($page, 'title'), 'item' => $canonical],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script><?php endif; ?>
</head>
<body class="page-<?= e($page['slug'] ?? 'home') ?>">
<a class="skip-link" href="#main-content"><?= e(__('skip_content')) ?></a>
<div class="scroll-progress" aria-hidden="true"><span></span></div>
<header class="site-header" data-header>
    <div class="header-shell">
        <a class="brand" href="<?= e(localized_url('/')) ?>" aria-label="<?= e(__('home')) ?>">
            <span class="brand-mark"><img src="<?= e(url('/assets/images/logo-maha.png')) ?>" alt="" width="254" height="362"></span>
            <span class="brand-copy">
                <strong><?= locale() === 'ar' ? 'شركة المها' : 'AL-MAHA CO.' ?></strong>
                <small><?= locale() === 'ar' ? 'للخدمات النفطية' : 'OIL SERVICES' ?></small>
            </span>
        </a>
        <nav class="desktop-nav" aria-label="<?= e(__('menu')) ?>">
            <a class="<?= is_active_path('/') ? 'active' : '' ?>" href="<?= e(localized_url('/')) ?>"><?= e(__('home')) ?></a>
            <?php if ($hasPublishedAbout): ?>
            <div class="nav-dropdown">
                <button class="<?= $isAboutActive ? 'active' : '' ?>" type="button" aria-expanded="false"><?= e(__('about')) ?> <span aria-hidden="true">⌄</span></button>
                <div class="nav-panel">
                    <?php if ($isPublished('about')): ?><a class="<?= is_active_path('/about') ? 'active' : '' ?>" href="<?= e(localized_url('/about')) ?>"><?= locale() === 'ar' ? 'عن المها' : 'About Al-Maha' ?></a><?php endif; ?>
                    <?php if ($isPublished('vision-mission')): ?><a class="<?= is_active_path('/vision-mission') ? 'active' : '' ?>" href="<?= e(localized_url('/vision-mission')) ?>"><?= locale() === 'ar' ? 'رؤيتنا ورسالتنا وقيمنا' : 'Our Vision, Mission & Values' ?></a><?php endif; ?>
                    <?php if ($isPublished('capabilities')): ?><a class="<?= is_active_path('/capabilities') ? 'active' : '' ?>" href="<?= e(localized_url('/capabilities')) ?>"><?= locale() === 'ar' ? 'قدراتنا التشغيلية' : 'Our Operational Capabilities' ?></a><?php endif; ?>
                    <?php if ($isPublished('sectors')): ?><a class="<?= is_active_path('/sectors') ? 'active' : '' ?>" href="<?= e(localized_url('/sectors')) ?>"><?= locale() === 'ar' ? 'قطاعتنا' : 'Our Sectors' ?></a><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($hasPublishedService): ?>
            <div class="nav-dropdown">
                <button type="button" aria-expanded="false"><?= e(__('services')) ?> <span aria-hidden="true">⌄</span></button>
                <div class="nav-panel">
                    <?php if ($isPublished('business-lines')): ?><a href="<?= e(localized_url('/business-lines')) ?>"><?= e(__('services')) ?></a><?php endif; ?>
                    <?php if ($isPublished('oil-services')): ?>
                    <a href="<?= e(localized_url('/oil-services')) ?>"><?= locale() === 'ar' ? 'الخدمات النفطية' : 'Oil Services' ?></a>
                    <?php endif; if ($isPublished('oxidized-asphalt')): ?>
                    <a href="<?= e(localized_url('/oxidized-asphalt')) ?>"><?= locale() === 'ar' ? 'الأسفلت المؤكسد' : 'Oxidized Asphalt' ?></a>
                    <?php endif; if ($isPublished('lubricating-oils')): ?>
                    <a href="<?= e(localized_url('/lubricating-oils')) ?>"><?= locale() === 'ar' ? 'زيوت التزييت' : 'Lubricating Oils' ?></a>
                    <?php endif; if ($isPublished('petroleum-supplies')): ?>
                    <a href="<?= e(localized_url('/petroleum-supplies')) ?>"><?= locale() === 'ar' ? 'التجهيزات النفطية' : 'Petroleum Supplies' ?></a>
                    <?php endif; if ($isPublished('general-services')): ?>
                    <a href="<?= e(localized_url('/general-services')) ?>"><?= locale() === 'ar' ? 'الخدمات العامة' : 'General Services' ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; if ($isPublished('projects')): ?>
            <a class="<?= is_active_path('/projects') ? 'active' : '' ?>" href="<?= e(localized_url('/projects')) ?>"><?= e(__('projects')) ?></a>
            <?php endif; if ($isPublished('partnerships')): ?>
            <a class="<?= is_active_path('/partnerships') ? 'active' : '' ?>" href="<?= e(localized_url('/partnerships')) ?>"><?= e(__('partnerships')) ?></a>
            <?php endif; ?>
        </nav>
        <div class="header-actions">
            <a class="lang-switch" href="<?= e(url('/lang/' . (locale() === 'ar' ? 'en' : 'ar') . '?return=' . rawurlencode($alternatePath))) ?>" lang="<?= locale() === 'ar' ? 'en' : 'ar' ?>"><?= e(__('switch_language')) ?></a>
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="<?= e(__('theme')) ?>"><span class="sun">☀</span><span class="moon">☾</span></button>
            <?php if ($isPublished('contact')): ?><a class="header-cta" href="<?= e(localized_url('/contact')) ?>"><?= e(__('request_quote')) ?></a><?php endif; ?>
            <button class="menu-toggle" type="button" data-menu-open aria-label="<?= e(__('menu')) ?>" aria-expanded="false"><span></span><span></span></button>
        </div>
    </div>
</header>
<div class="mobile-menu" data-mobile-menu aria-hidden="true">
    <div class="mobile-menu-head">
        <span><?= locale() === 'ar' ? 'التنقل' : 'Navigation' ?></span>
        <button type="button" data-menu-close aria-label="<?= e(__('close')) ?>">×</button>
    </div>
    <nav aria-label="<?= e(__('menu')) ?>">
        <a href="<?= e(localized_url('/')) ?>"><span>01</span><?= e(__('home')) ?></a>
        <?php if ($hasPublishedAbout): ?>
        <div class="mobile-nav-group">
            <p><?= e(__('about')) ?></p>
            <?php if ($isPublished('about')): ?><a href="<?= e(localized_url('/about')) ?>"><span>02</span><?= locale() === 'ar' ? 'عن المها' : 'About Al-Maha' ?></a><?php endif; ?>
            <?php if ($isPublished('vision-mission')): ?><a href="<?= e(localized_url('/vision-mission')) ?>"><span>03</span><?= locale() === 'ar' ? 'رؤيتنا ورسالتنا وقيمنا' : 'Our Vision, Mission & Values' ?></a><?php endif; ?>
            <?php if ($isPublished('capabilities')): ?><a href="<?= e(localized_url('/capabilities')) ?>"><span>04</span><?= locale() === 'ar' ? 'قدراتنا التشغيلية' : 'Our Operational Capabilities' ?></a><?php endif; ?>
            <?php if ($isPublished('sectors')): ?><a href="<?= e(localized_url('/sectors')) ?>"><span>05</span><?= locale() === 'ar' ? 'قطاعتنا' : 'Our Sectors' ?></a><?php endif; ?>
        </div>
        <?php endif; if ($isPublished('oil-services')): ?>
        <a href="<?= e(localized_url('/oil-services')) ?>"><span>06</span><?= locale() === 'ar' ? 'الخدمات النفطية' : 'Oil Services' ?></a>
        <?php endif; if ($isPublished('oxidized-asphalt')): ?>
        <a href="<?= e(localized_url('/oxidized-asphalt')) ?>"><span>07</span><?= locale() === 'ar' ? 'الأسفلت المؤكسد' : 'Oxidized Asphalt' ?></a>
        <?php endif; if ($isPublished('lubricating-oils')): ?>
        <a href="<?= e(localized_url('/lubricating-oils')) ?>"><span>08</span><?= locale() === 'ar' ? 'زيوت التزييت' : 'Lubricating Oils' ?></a>
        <?php endif; if ($isPublished('petroleum-supplies')): ?>
        <a href="<?= e(localized_url('/petroleum-supplies')) ?>"><span>09</span><?= locale() === 'ar' ? 'التجهيزات النفطية' : 'Petroleum Supplies' ?></a>
        <?php endif; if ($isPublished('general-services')): ?>
        <a href="<?= e(localized_url('/general-services')) ?>"><span>10</span><?= locale() === 'ar' ? 'الخدمات العامة' : 'General Services' ?></a>
        <?php endif; if ($isPublished('projects')): ?>
        <a href="<?= e(localized_url('/projects')) ?>"><span>11</span><?= e(__('projects')) ?></a>
        <?php endif; if ($isPublished('contact')): ?>
        <a href="<?= e(localized_url('/contact')) ?>"><span>12</span><?= e(__('contact')) ?></a>
        <?php endif; ?>
    </nav>
</div>
<main id="main-content"><?= $content ?></main>
<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="brand footer-logo">
                <span class="brand-mark"><img src="<?= e(url('/assets/images/logo-maha.png')) ?>" alt="" width="254" height="362" loading="lazy"></span>
                <span class="brand-copy"><strong><?= locale() === 'ar' ? 'شركة المها' : 'AL-MAHA CO.' ?></strong><small><?= locale() === 'ar' ? 'للخدمات النفطية' : 'OIL SERVICES' ?></small></span>
            </div>
            <p><?= locale() === 'ar'
                ? 'شركة عراقية خاصة تعمل في الخدمات النفطية والمنتجات الصناعية المساندة والخدمات العامة.'
                : 'A private Iraqi company operating across oil services, supporting industrial products and general services.' ?></p>
        </div>
        <div><h2><?= locale() === 'ar' ? 'الشركة' : 'Company' ?></h2><?php if ($isPublished('about')): ?><a href="<?= e(localized_url('/about')) ?>"><?= locale() === 'ar' ? 'عن المها' : 'About Al-Maha' ?></a><?php endif; ?><?php if ($isPublished('vision-mission')): ?><a href="<?= e(localized_url('/vision-mission')) ?>"><?= locale() === 'ar' ? 'رؤيتنا ورسالتنا وقيمنا' : 'Our Vision, Mission & Values' ?></a><?php endif; ?><?php if ($isPublished('capabilities')): ?><a href="<?= e(localized_url('/capabilities')) ?>"><?= locale() === 'ar' ? 'قدراتنا التشغيلية' : 'Our Operational Capabilities' ?></a><?php endif; ?><?php if ($isPublished('sectors')): ?><a href="<?= e(localized_url('/sectors')) ?>"><?= locale() === 'ar' ? 'قطاعتنا' : 'Our Sectors' ?></a><?php endif; ?></div>
        <div><h2><?= e(__('services')) ?></h2><?php if ($isPublished('oxidized-asphalt')): ?><a href="<?= e(localized_url('/oxidized-asphalt')) ?>"><?= locale() === 'ar' ? 'الأسفلت المؤكسد' : 'Oxidized Asphalt' ?></a><?php endif; ?><?php if ($isPublished('lubricating-oils')): ?><a href="<?= e(localized_url('/lubricating-oils')) ?>"><?= locale() === 'ar' ? 'زيوت التزييت' : 'Lubricating Oils' ?></a><?php endif; ?><?php if ($isPublished('petroleum-supplies')): ?><a href="<?= e(localized_url('/petroleum-supplies')) ?>"><?= locale() === 'ar' ? 'المواد النفطية' : 'Petroleum Materials' ?></a><?php endif; ?><?php if ($isPublished('general-services')): ?><a href="<?= e(localized_url('/general-services')) ?>"><?= locale() === 'ar' ? 'الخدمات العامة' : 'General Services' ?></a><?php endif; ?></div>
        <div class="footer-contact"><h2><?= e(__('contact')) ?></h2><a dir="ltr" href="mailto:<?= e($settings['contact_email'] ?? 'info@almaha-group.com') ?>"><?= e($settings['contact_email'] ?? 'info@almaha-group.com') ?></a><address><?= e(locale() === 'ar' ? ($settings['contact_address_ar'] ?? 'العراق - بغداد') : ($settings['contact_address_en'] ?? 'Iraq - Baghdad')) ?></address><?php if ($isPublished('contact')): ?><a class="text-link" href="<?= e(localized_url('/contact')) ?>"><?= e(__('request_quote')) ?> <span aria-hidden="true">↗</span></a><?php endif; ?></div>
    </div>
    <div class="footer-base">
        <p>© <?= date('Y') ?> <?= locale() === 'ar' ? 'شركة المها' : 'Al-Maha Co.' ?> — <?= e(__('all_rights')) ?></p>
        <div><?php if ($isPublished('privacy')): ?><a href="<?= e(localized_url('/privacy')) ?>"><?= e(__('privacy')) ?></a><?php endif; ?><?php if ($isPublished('terms')): ?><a href="<?= e(localized_url('/terms')) ?>"><?= e(__('terms')) ?></a><?php endif; ?><?php if ($isPublished('company-profile')): ?><a class="footer-profile-pulse" href="<?= e(localized_url('/company-profile')) ?>"><?= e(__('profile')) ?></a><?php endif; ?></div>
    </div>
</footer>
<script src="<?= e(url('/assets/js/site' . $assetSuffix . '.js')) ?>" defer></script>
</body>
</html>
