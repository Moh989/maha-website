<?php $isAr = locale() === 'ar'; ?>
<section class="inner-hero"><div class="inner-hero-grid" aria-hidden="true"></div><div class="inner-hero-content"><p class="eyebrow"><span></span>BUSINESS LINES</p><h1><?= e(localized_field($page, 'title')) ?></h1><p><?= e(localized_field($page, 'excerpt')) ?></p></div></section>
<section class="service-index section">
    <?php foreach ($services as $index => $service): ?><a class="service-index-row reveal" href="<?= e(localized_url('/' . $service['slug'])) ?>"><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><div><h2><?= e(localized_field($service, 'title')) ?></h2><p><?= e(localized_field($service, 'summary')) ?></p></div><i aria-hidden="true">↗</i></a><?php endforeach; ?>
</section>
