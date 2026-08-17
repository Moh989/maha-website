<?php $isAr = locale() === 'ar'; $old = $old ?? []; $errors = $errors ?? []; ?>
<section class="inner-hero contact-hero"><div class="inner-hero-grid" aria-hidden="true"></div><div class="inner-hero-content"><p class="eyebrow"><span></span>CONTACT / REQUEST</p><h1><?= e(localized_field($page, 'title')) ?></h1><p><?= e(localized_field($page, 'excerpt')) ?></p></div></section>
<section class="contact-section section">
    <div class="contact-info reveal">
        <p class="eyebrow dark"><span></span><?= $isAr ? 'معلومات الشركة' : 'Company Details' ?></p>
        <h2><?= $isAr ? 'ابدأ محادثة مهنية مباشرة.' : 'Start a direct professional conversation.' ?></h2>
        <div class="contact-lines"><a href="tel:+9647852101010"><span><?= $isAr ? 'الهاتف' : 'Phone' ?></span><strong dir="ltr">+964 785 210 1010</strong></a><a href="mailto:info@almaha-group.com"><span><?= $isAr ? 'البريد الإلكتروني' : 'Email' ?></span><strong dir="ltr">info@almaha-group.com</strong></a><div><span><?= $isAr ? 'المقر' : 'Headquarters' ?></span><strong><?= $isAr ? 'الجادرية - بغداد، العراق' : 'Al-Jadriya - Baghdad, Iraq' ?></strong></div></div>
        <p class="data-note"><?= $isAr ? 'تُستخدم بيانات هذا النموذج للرد على الطلب فقط وفق سياسة الخصوصية.' : 'Form data is used only to respond to the request, as described in the privacy policy.' ?></p>
    </div>
    <form class="contact-form reveal" method="post" action="<?= e(locale() === 'en' ? url('/en/contact') : url('/contact')) ?>" novalidate data-validate>
        <?= csrf_field() ?>
        <div class="honeypot" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <?php if ($flashSuccess): ?><div class="form-alert success" role="status"><?= e($flashSuccess) ?></div><?php endif; ?>
        <?php if (!empty($errors['form'])): ?><div class="form-alert error" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>
        <div class="form-grid">
            <?php
            $fields = [
                'name' => [$isAr ? 'الاسم الكامل' : 'Full name', 'text', true],
                'company' => [$isAr ? 'الشركة أو الجهة' : 'Company or entity', 'text', false],
                'email' => [$isAr ? 'البريد الإلكتروني' : 'Email address', 'email', true],
                'phone' => [$isAr ? 'رقم الهاتف' : 'Phone number', 'tel', false],
                'country' => [$isAr ? 'الدولة' : 'Country', 'text', true],
                'subject' => [$isAr ? 'عنوان الرسالة' : 'Subject', 'text', true],
            ]; foreach ($fields as $name => [$label, $type, $required]): ?>
                <label class="field"><span><?= e($label) ?><?= $required ? ' *' : '' ?></span><input type="<?= e($type) ?>" name="<?= e($name) ?>" value="<?= e($old[$name] ?? '') ?>" <?= $required ? 'required' : '' ?> maxlength="<?= $name === 'subject' ? '200' : '160' ?>" aria-describedby="err-<?= e($name) ?>"><small id="err-<?= e($name) ?>"><?= e($errors[$name] ?? '') ?></small></label>
            <?php endforeach; ?>
            <label class="field"><span><?= $isAr ? 'نوع الطلب *' : 'Request type *' ?></span><select name="request_type" required><option value=""><?= $isAr ? 'اختر' : 'Select' ?></option><?php foreach ([$isAr ? 'طلب عرض' : 'Request for quotation', $isAr ? 'فرصة تعاون' : 'Cooperation opportunity', $isAr ? 'استفسار فني' : 'Technical inquiry', $isAr ? 'استفسار عام' : 'General inquiry'] as $option): ?><option <?= ($old['request_type'] ?? '') === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select><small><?= e($errors['request_type'] ?? '') ?></small></label>
            <label class="field"><span><?= $isAr ? 'الخدمة المطلوبة *' : 'Required service *' ?></span><select name="service" required><option value=""><?= $isAr ? 'اختر' : 'Select' ?></option><?php foreach ([$isAr ? 'الخدمات النفطية' : 'Oil services', $isAr ? 'الأسفلت المؤكسد' : 'Oxidized asphalt', $isAr ? 'زيوت التزييت' : 'Lubricating oils', $isAr ? 'التجهيزات النفطية' : 'Petroleum supplies', $isAr ? 'الخدمات العامة' : 'General services'] as $option): ?><option <?= ($old['service'] ?? '') === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select><small><?= e($errors['service'] ?? '') ?></small></label>
        </div>
        <label class="field full"><span><?= $isAr ? 'نص الرسالة *' : 'Message *' ?></span><textarea name="message" rows="7" required minlength="20" maxlength="5000"><?= e($old['message'] ?? '') ?></textarea><small><?= e($errors['message'] ?? '') ?></small></label>
        <label class="consent"><input type="checkbox" name="privacy" value="1" required <?= !empty($old['privacy']) ? 'checked' : '' ?>><span><?= $isAr ? 'أوافق على معالجة بياناتي لغرض الرد على الطلب وفق' : 'I agree to the processing of my data to respond to this request under the' ?> <a href="<?= e(localized_url('/privacy')) ?>"><?= e(__('privacy')) ?></a>.</span></label>
        <?php if (!empty($errors['privacy'])): ?><small class="field-error"><?= e($errors['privacy']) ?></small><?php endif; ?>
        <button class="button primary submit-button" type="submit"><?= $isAr ? 'إرسال الطلب' : 'Send request' ?> <span aria-hidden="true">↗</span></button>
    </form>
</section>
