<?php $isAr = locale() === 'ar'; ?>
<section class="profile-download">
    <div class="profile-shape" aria-hidden="true"></div>
    <div class="profile-copy"><p class="eyebrow"><span></span>COMPANY PROFILE / 2026</p><h1><?= e(localized_field($page, 'title')) ?></h1><p><?= e(localized_field($page, 'excerpt')) ?></p><a class="button primary" href="<?= e(url('/assets/docs/almaha-company-profile-2026.pdf')) ?>" download><?= e(__('download_profile')) ?> <span aria-hidden="true">↓</span></a><small>PDF · 2026 · 32 <?= $isAr ? 'صفحة' : 'pages' ?></small></div>
    <div class="profile-cover"><img src="<?= e(url('/assets/images/logo-maha.png')) ?>" width="254" height="362" alt=""><strong>AL-MAHA CO.</strong><span>COMPANY PROFILE / 2026</span></div>
</section>
