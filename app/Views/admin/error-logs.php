<?php
$levelLabels = ['critical' => 'حرج', 'error' => 'خطأ', 'warning' => 'تحذير', 'notice' => 'ملاحظة'];
$prettyJson = static function (mixed $value): string {
    $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    return is_string($encoded) ? $encoded : '';
};
$queryString = static function (array $overrides = []) use ($q, $level, $source): string {
    return http_build_query(array_filter(array_merge([
        'q' => $q,
        'level' => $level,
        'source' => $source,
    ], $overrides), static fn (mixed $value): bool => $value !== '' && $value !== null));
};
$formatBytes = static function (int $bytes): string {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
};
?>

<section class="log-overview" aria-label="ملخص سجل الأخطاء">
    <div class="log-summary-card critical"><span>أخطاء حرجة</span><strong><?= e($stats['critical'] ?? 0) ?></strong></div>
    <div class="log-summary-card error"><span>أخطاء</span><strong><?= e($stats['error'] ?? 0) ?></strong></div>
    <div class="log-summary-card warning"><span>تحذيرات</span><strong><?= e($stats['warning'] ?? 0) ?></strong></div>
    <div class="log-summary-card notice"><span>ملاحظات</span><strong><?= e($stats['notice'] ?? 0) ?></strong></div>
</section>

<section class="admin-panel log-health">
    <div>
        <p>يُعرض أحدث <?= e(number_format($limit)) ?> سجل كحد أقصى</p>
        <h2><?= e(number_format($total)) ?> نتيجة مطابقة من <?= e(number_format($scanned)) ?> سجل مقروء</h2>
    </div>
    <div class="log-file-health">
        <?php foreach ($files as $fileName => $fileInfo): ?>
            <span class="<?= ($fileInfo['exists'] && $fileInfo['readable']) ? 'healthy' : 'unhealthy' ?>">
                <b><?= e($fileName) ?></b>
                <?= $fileInfo['exists'] ? e($formatBytes((int) $fileInfo['size'])) : 'غير موجود' ?>
            </span>
        <?php endforeach; ?>
    </div>
</section>

<section class="admin-toolbar log-toolbar">
    <form method="get" action="<?= e(url('/admin/error-logs')) ?>">
        <label>
            <span class="sr-only">البحث في سجل الأخطاء</span>
            <input type="search" name="q" value="<?= e($q) ?>" maxlength="200" placeholder="رسالة الخطأ، الرابط أو Request ID">
        </label>
        <label>
            <span class="sr-only">درجة الخطأ</span>
            <select name="level">
                <option value="">كل الدرجات</option>
                <?php foreach ($levelLabels as $value => $label): ?><option value="<?= e($value) ?>" <?= $level === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </label>
        <label>
            <span class="sr-only">مصدر السجل</span>
            <select name="source">
                <option value="">كل المصادر</option>
                <option value="application" <?= $source === 'application' ? 'selected' : '' ?>>سجل التطبيق</option>
                <option value="php-native" <?= $source === 'php-native' ? 'selected' : '' ?>>سجل PHP الاحتياطي</option>
            </select>
        </label>
        <button type="submit">بحث وتصفية</button>
        <?php if ($q !== '' || $level !== '' || $source !== ''): ?><a class="log-reset" href="<?= e(url('/admin/error-logs')) ?>">إلغاء التصفية</a><?php endif; ?>
    </form>
    <a class="admin-primary" href="<?= e(url('/admin/error-logs?' . $queryString())) ?>">تحديث السجل ↻</a>
</section>

<section class="log-list" aria-live="polite">
    <?php foreach ($entries as $entry):
        $request = is_array($entry['request'] ?? null) ? $entry['request'] : [];
        $sourceInfo = is_array($entry['source'] ?? null) ? $entry['source'] : [];
        $exception = is_array($entry['exception'] ?? null) ? $entry['exception'] : [];
        $context = is_array($entry['context'] ?? null) ? $entry['context'] : [];
        $runtime = is_array($entry['runtime'] ?? null) ? $entry['runtime'] : [];
        $entryLevel = (string) ($entry['level'] ?? 'error');
        $path = (string) ($request['path'] ?? '');
        $method = (string) ($request['method'] ?? '');
        $status = (string) ($context['status'] ?? '');
    ?>
        <article class="log-entry <?= e($entryLevel) ?>">
            <div class="log-entry-main">
                <div class="log-entry-heading">
                    <span class="log-level"><?= e($levelLabels[$entryLevel] ?? $entryLevel) ?></span>
                    <strong dir="ltr"><?= e($entry['event'] ?? 'APPLICATION_LOG') ?></strong>
                    <time dir="ltr" datetime="<?= e($entry['timestamp'] ?? '') ?>"><?= e($entry['timestamp'] ?: 'وقت غير متوفر') ?></time>
                </div>
                <p><?= e($entry['message'] ?? '') ?></p>
                <div class="log-entry-meta">
                    <?php if ($method !== '' || $path !== ''): ?><span dir="ltr"><b><?= e($method) ?></b> <?= e($path) ?></span><?php endif; ?>
                    <?php if ($status !== ''): ?><span dir="ltr">HTTP <?= e($status) ?></span><?php endif; ?>
                    <?php if (!empty($entry['request_id'])): ?><span dir="ltr">Request ID: <?= e($entry['request_id']) ?></span><?php endif; ?>
                    <span dir="ltr"><?= e($entry['log_file'] ?? '') ?></span>
                </div>
            </div>
            <details class="log-details">
                <summary>عرض التفاصيل الدقيقة</summary>
                <div class="log-detail-grid">
                    <section>
                        <h3>الطلب</h3>
                        <?php if ($request): ?><pre dir="ltr"><?= e($prettyJson($request)) ?></pre><?php else: ?><p>لا توجد بيانات طلب مرتبطة.</p><?php endif; ?>
                    </section>
                    <section>
                        <h3>المصدر وبيئة التشغيل</h3>
                        <pre dir="ltr"><?= e($prettyJson(['source' => $sourceInfo, 'runtime' => $runtime, 'log_file' => $entry['log_file'] ?? ''])) ?></pre>
                    </section>
                    <?php if ($exception): ?><section class="wide"><h3>الاستثناء والتتبع</h3><pre dir="ltr"><?= e($prettyJson($exception)) ?></pre></section><?php endif; ?>
                    <?php if ($context): ?><section class="wide"><h3>السياق</h3><pre dir="ltr"><?= e($prettyJson($context)) ?></pre></section><?php endif; ?>
                </div>
            </details>
        </article>
    <?php endforeach; ?>

    <?php if (!$entries): ?>
        <div class="admin-panel empty-state">لا توجد أخطاء مطابقة. جرّب إزالة التصفية أو حدّث السجل.</div>
    <?php endif; ?>
</section>

<?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="صفحات سجل الأخطاء">
        <?php for ($index = 1; $index <= $pages; $index++): ?>
            <a class="<?= $index === $page ? 'active' : '' ?>" href="<?= e(url('/admin/error-logs?' . $queryString(['page' => $index]))) ?>"><?= e($index) ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
