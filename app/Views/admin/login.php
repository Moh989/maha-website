<main class="login-shell">
    <section class="login-visual"><div class="login-grid"></div><div><p>AL-MAHA CO.</p><h1>بوابة إدارة المحتوى المؤسسي.</h1><span>جلسة محمية · صلاحيات أدوار · سجل عمليات</span></div></section>
    <section class="login-form-wrap">
        <a class="login-logo" href="<?= e(url('/')) ?>"><span><img src="<?= e(url('/assets/images/logo-maha.png')) ?>" width="254" height="362" alt=""></span><strong>شركة المها<small>للخدمات النفطية</small></strong></a>
        <form method="post" action="<?= e(url('/admin/login')) ?>" class="login-form">
            <?= csrf_field() ?>
            <p class="login-kicker">تسجيل الدخول</p><h2>مرحباً بعودتك</h2><p>أدخل بيانات حساب الإدارة للوصول إلى لوحة التحكم.</p>
            <?php if (!empty($error)): ?><div class="admin-alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <label><span>البريد الإلكتروني</span><input type="email" name="email" required autocomplete="username" dir="ltr"></label>
            <label><span>كلمة المرور</span><input type="password" name="password" required autocomplete="current-password" minlength="12" dir="ltr"></label>
            <button type="submit">دخول آمن <span aria-hidden="true">←</span></button>
        </form>
    </section>
</main>
