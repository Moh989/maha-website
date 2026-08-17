<?php $isAr = locale() === 'ar'; ?>
<section class="inner-hero"><div class="inner-hero-grid" aria-hidden="true"></div><div class="inner-hero-content"><p class="eyebrow"><span></span>MEDIA GALLERY</p><h1><?= e(localized_field($page, 'title')) ?></h1><p><?= e(localized_field($page, 'excerpt')) ?></p></div></section>
<section class="gallery-grid section">
    <?php foreach ($media as $item): if (!str_starts_with($item['mime_type'], 'image/')) continue; ?><figure class="reveal"><img src="<?= e(url('/media/' . $item['id'])) ?>" loading="lazy" alt="<?= e(localized_field($item, 'alt')) ?>"><figcaption><?= e(localized_field($item, 'alt')) ?></figcaption></figure><?php endforeach; ?>
    <?php if (!$media): ?><p><?= $isAr ? 'لا توجد صور منشورة حالياً.' : 'No images are currently published.' ?></p><?php endif; ?>
</section>
