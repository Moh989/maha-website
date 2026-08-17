<?php $isEdit = !empty($item['id']); $old = $old ?? []; ?>
<form class="admin-edit-form" method="post" action="<?= e(url('/admin/' . $resource . ($isEdit ? '/' . $item['id'] . '/edit' : '/create'))) ?>">
    <?= csrf_field() ?>
    <div class="edit-actions"><a href="<?= e(url('/admin/' . $resource)) ?>">← العودة</a><div><?php if ($isEdit): ?><a class="preview-link" href="<?= e(url('/admin/' . $resource . '/' . $item['id'] . '/preview')) ?>" target="_blank">معاينة ↗</a><?php endif; ?><button class="admin-primary" type="submit">حفظ التغييرات</button></div></div>
    <?php if ($errors): ?><div class="admin-alert error">يرجى تصحيح الحقول الموضحة أدناه.</div><?php endif; ?>
    <div class="edit-layout"><section class="edit-main admin-panel"><div class="panel-head"><div><p>المحتوى</p><h2><?= $isEdit ? 'تعديل العنصر' : 'إضافة عنصر جديد' ?></h2></div></div><div class="admin-form-grid">
        <?php foreach ($resourceConfig['fields'] as $name => $field):
            $value = array_key_exists($name, $old) ? $old[$name] : ($item[$name] ?? '');
            $type = $field['type']; $wide = in_array($type, ['textarea', 'textarea-rich', 'readonly-textarea'], true);
        ?><label class="admin-field <?= $wide ? 'wide' : '' ?>"><span><?= e($field['label']) ?><?= ($field['required'] ?? false) ? ' *' : '' ?></span>
            <?php if (in_array($type, ['textarea', 'textarea-rich', 'readonly-textarea'], true)): ?><textarea name="<?= e($name) ?>" rows="<?= $type === 'textarea-rich' ? '12' : '5' ?>" dir="<?= e($field['dir'] ?? 'auto') ?>" <?= str_starts_with($type, 'readonly') ? 'readonly' : '' ?>><?= e($value) ?></textarea>
            <?php elseif ($type === 'select' || $type === 'status'): $options = $type === 'status' ? ['draft' => 'مسودة', 'published' => 'منشور', 'hidden' => 'مخفي'] : $field['options']; ?><select name="<?= e($name) ?>"><?php foreach ($options as $optionValue => $optionLabel): ?><option value="<?= e($optionValue) ?>" <?= (string) $value === (string) $optionValue ? 'selected' : '' ?>><?= e($optionLabel) ?></option><?php endforeach; ?></select>
            <?php elseif ($type === 'roles'): ?><select name="<?= e($name) ?>" required><?php foreach ($roles as $role): ?><option value="<?= e($role['id']) ?>" <?= (string) $value === (string) $role['id'] ? 'selected' : '' ?>><?= e($role['name_ar']) ?></option><?php endforeach; ?></select>
            <?php elseif ($type === 'checkbox'): ?><span class="switch"><input type="checkbox" name="<?= e($name) ?>" value="1" <?= $value ? 'checked' : '' ?>><i></i></span>
            <?php elseif ($type === 'readonly'): ?><input type="text" value="<?= e($value) ?>" readonly dir="<?= e($field['dir'] ?? 'auto') ?>">
            <?php else: ?><input type="<?= e(in_array($type, ['email', 'password', 'number', 'date'], true) ? $type : 'text') ?>" name="<?= e($name) ?>" value="<?= $type === 'password' ? '' : e($value) ?>" dir="<?= e($field['dir'] ?? 'auto') ?>" <?= ($field['required'] ?? false) && !($type === 'password' && $isEdit) ? 'required' : '' ?> <?= $type === 'number' ? 'min="0"' : '' ?> placeholder="<?= $type === 'password' && $isEdit ? 'اتركه فارغاً للاحتفاظ بالحالية' : '' ?>"><?php endif; ?>
            <?php if (!empty($errors[$name])): ?><small><?= e($errors[$name]) ?></small><?php endif; ?>
        </label><?php endforeach; ?>
    </div></section>
    <aside class="edit-side">
        <section class="admin-panel help-panel"><h2>إرشادات النشر</h2><ul><li>استخدم المحتوى الرسمي فقط.</li><li>لا تضف أرقاماً أو شهادات أو أسماء عملاء غير موثقة.</li><li>احفظ كمسودة قبل اكتمال الترجمة.</li><li>استخدم ترتيباً رقمياً للتحكم في موضع العنصر.</li></ul></section>
        <?php if ($resourceConfig['seo'] ?? false): ?><section class="admin-panel seo-panel"><h2>تحسين محركات البحث</h2><?php foreach ([['seo_title_ar','عنوان SEO العربي'],['seo_title_en','عنوان SEO الإنجليزي'],['seo_description_ar','وصف SEO العربي'],['seo_description_en','وصف SEO الإنجليزي'],['og_image','مسار صورة المشاركة']] as [$name,$label]): ?><label class="admin-field"><span><?= e($label) ?></span><?php if (str_contains($name, 'description')): ?><textarea name="<?= e($name) ?>" rows="4" dir="<?= str_ends_with($name, '_en') ? 'ltr' : 'auto' ?>"><?= e($seo[$name] ?? '') ?></textarea><?php else: ?><input type="text" name="<?= e($name) ?>" value="<?= e($seo[$name] ?? '') ?>" dir="<?= str_ends_with($name, '_en') || $name === 'og_image' ? 'ltr' : 'auto' ?>"><?php endif; ?></label><?php endforeach; ?></section><?php endif; ?>
    </aside></div>
</form>
