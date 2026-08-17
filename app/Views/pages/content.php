<?php
$isAr = locale() === 'ar';
$currentSlug = (string) ($page['slug'] ?? '');
$sectionLabels = [
    'about' => ['ar' => 'عن المها', 'en' => 'About Al-Maha'],
    'vision-mission' => ['ar' => 'رؤيتنا ورسالتنا وقيمنا', 'en' => 'Our Vision, Mission & Values'],
    'capabilities' => ['ar' => 'قدراتنا التشغيلية', 'en' => 'Our Operational Capabilities'],
    'sectors' => ['ar' => 'قطاعتنا', 'en' => 'Our Sectors'],
];
$sectionLabel = $sectionLabels[$currentSlug][$isAr ? 'ar' : 'en'] ?? null;
$aboutPages = [
    ['slug' => 'about', 'ar' => 'من نحن', 'en' => 'About Al-Maha'],
    ['slug' => 'vision-mission', 'ar' => 'الرؤية والرسالة والقيم', 'en' => 'Vision, Mission & Values'],
    ['slug' => 'capabilities', 'ar' => 'القدرات التشغيلية', 'en' => 'Operational Capabilities'],
    ['slug' => 'sectors', 'ar' => 'القطاعات', 'en' => 'Sectors'],
];
?>
<section class="inner-hero">
    <div class="inner-hero-grid" aria-hidden="true"></div>
    <div class="inner-hero-content"><p class="eyebrow"><span></span>AL-MAHA CO.</p><h1><?= e(localized_field($page, 'title')) ?></h1><p><?= e(localized_field($page, 'excerpt')) ?></p></div>
</section>
<nav class="breadcrumbs" aria-label="<?= $isAr ? 'مسار الصفحة' : 'Breadcrumb' ?>"><a href="<?= e(localized_url('/')) ?>"><?= e(__('home')) ?></a><span>/</span><span aria-current="page"><?= e(localized_field($page, 'title')) ?></span></nav>
<?php if ($currentSlug === 'about'): ?>
<nav class="about-section-nav section" aria-label="<?= $isAr ? 'صفحات من نحن' : 'About Al-Maha pages' ?>">
    <p class="eyebrow dark"><span></span><?= $isAr ? 'تعرّف على المها' : 'Discover Al-Maha' ?></p>
    <div class="about-section-links">
        <?php foreach ($aboutPages as $index => $aboutPage): ?>
        <a class="<?= $aboutPage['slug'] === $currentSlug ? 'active' : '' ?>" href="<?= e(localized_url('/' . $aboutPage['slug'])) ?>"<?= $aboutPage['slug'] === $currentSlug ? ' aria-current="page"' : '' ?>>
            <span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <strong><?= e($aboutPage[$isAr ? 'ar' : 'en']) ?></strong>
            <i aria-hidden="true">↗</i>
        </a>
        <?php endforeach; ?>
    </div>
</nav>
<?php endif; ?>
<section class="content-page section<?= $sectionLabel ? '' : ' content-page-wide' ?>">
    <?php if ($sectionLabel): ?>
    <aside class="content-outline-aside">
        <span class="content-outline-label"><?= e($sectionLabel) ?></span>
    </aside>
    <?php endif; ?>
    <article class="prose reveal"><?= nl2br(e(localized_field($page, 'body'))) ?></article>
</section>
<section class="compact-cta"><div><h2><?= $isAr ? 'هل لديكم متطلبات تشغيلية أو فرصة تعاون؟' : 'Have an operating requirement or cooperation opportunity?' ?></h2><a class="button primary" href="<?= e(localized_url('/contact')) ?>"><?= e(__('contact')) ?> <span aria-hidden="true">↗</span></a></div></section>
