<?php $isAr = locale() === 'ar'; ?>
<section class="inner-hero projects-hero"><div class="inner-hero-grid" aria-hidden="true"></div><div class="inner-hero-content"><p class="eyebrow"><span></span>DOCUMENTED CONTRACTS</p><h1><?= e(localized_field($page, 'title')) ?></h1><p><?= e(localized_field($page, 'excerpt')) ?></p></div></section>
<section class="projects-list section">
    <?php foreach ($projects as $index => $project): ?><article class="project-full reveal"><div class="project-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></div><div><p class="eyebrow dark"><span></span><?= $isAr ? 'عقد منفذ' : 'Executed Contract' ?></p><h2><?= e(localized_field($project, 'title')) ?></h2><p><?= e(localized_field($project, 'summary')) ?></p></div></article><?php endforeach; ?>
</section>
