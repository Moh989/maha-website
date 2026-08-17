<?php

declare(strict_types=1);

/**
 * End-to-end HTTP smoke test for the public website and CMS.
 *
 * Usage:
 *   QA_ADMIN_EMAIL='...' QA_ADMIN_PASSWORD='...' php tools/qa_http.php http://127.0.0.1:8080
 */

final class QaClient
{
    private string $cookieFile;

    public function __construct(private readonly string $baseUrl)
    {
        $cookie = tempnam(sys_get_temp_dir(), 'maha-qa-cookie-');
        if ($cookie === false) {
            throw new RuntimeException('Unable to create a temporary cookie jar.');
        }
        $this->cookieFile = $cookie;
    }

    public function __destruct()
    {
        if (is_file($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }

    /** @return array{status:int,headers:string,body:string,location:string} */
    public function request(string $method, string $path, array $data = [], bool $multipart = false): array
    {
        $handle = curl_init($this->baseUrl . $path);
        if ($handle === false) {
            throw new RuntimeException('Unable to initialize cURL.');
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'Al-Maha-QA/1.0',
        ]);
        if ($method === 'POST') {
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $multipart ? $data : http_build_query($data));
        }

        $response = curl_exec($handle);
        if (!is_string($response)) {
            $error = curl_error($handle);
            throw new RuntimeException('HTTP request failed: ' . $error);
        }
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($handle, CURLINFO_HEADER_SIZE);

        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        preg_match('/^Location:\s*(.+)$/mi', $headers, $location);

        return [
            'status' => $status,
            'headers' => $headers,
            'body' => $body,
            'location' => trim($location[1] ?? ''),
        ];
    }
}

/** @param list<string> $failures */
function check(bool $condition, string $label, array &$failures): void
{
    if ($condition) {
        echo "PASS  {$label}\n";
        return;
    }
    echo "FAIL  {$label}\n";
    $failures[] = $label;
}

function csrf(string $html): string
{
    if (!preg_match('/name="_token"\s+value="([a-f0-9]{64})"/', $html, $match)) {
        throw new RuntimeException('CSRF token not found in HTML.');
    }
    return $match[1];
}

/** @return array{id:int,edit:string,toggle:string,delete:string}|null */
function adminRow(string $html, string $needle): ?array
{
    $dom = new DOMDocument();
    @$dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('//tbody/tr') ?: [] as $row) {
        if (!str_contains($row->textContent, $needle)) {
            continue;
        }
        $result = ['id' => 0, 'edit' => '', 'toggle' => '', 'delete' => ''];
        $idCell = $xpath->query('./td[1]', $row)?->item(0);
        $result['id'] = (int) trim($idCell?->textContent ?? '0');
        foreach ($xpath->query('.//*[@href or @action]', $row) ?: [] as $element) {
            $target = (string) ($element->getAttribute('href') ?: $element->getAttribute('action'));
            if (str_ends_with($target, '/edit')) {
                $result['edit'] = parse_url($target, PHP_URL_PATH) ?: '';
            } elseif (str_ends_with($target, '/toggle')) {
                $result['toggle'] = parse_url($target, PHP_URL_PATH) ?: '';
            } elseif (str_ends_with($target, '/delete')) {
                $result['delete'] = parse_url($target, PHP_URL_PATH) ?: '';
            }
        }
        return $result;
    }
    return null;
}

function login(QaClient $client, string $email, string $password): array
{
    $page = $client->request('GET', '/admin/login');
    return $client->request('POST', '/admin/login', [
        '_token' => csrf($page['body']),
        'email' => $email,
        'password' => $password,
    ]);
}

$baseUrl = rtrim($argv[1] ?? 'http://127.0.0.1:8080', '/');
$adminEmail = getenv('QA_ADMIN_EMAIL') ?: '';
$adminPassword = getenv('QA_ADMIN_PASSWORD') ?: '';
if ($adminEmail === '' || $adminPassword === '') {
    fwrite(STDERR, "Set QA_ADMIN_EMAIL and QA_ADMIN_PASSWORD before running this test.\n");
    exit(2);
}

$client = new QaClient($baseUrl);
$failures = [];
$qaSuffix = bin2hex(random_bytes(3));

foreach ([
    '/' => [200, 'lang="ar" dir="rtl"'],
    '/en' => [200, 'lang="en" dir="ltr"'],
    '/about' => [200, '<h1'],
    '/en/about' => [200, '<h1'],
    '/missing-qa-route' => [404, '404'],
    '/assets/css/site.css' => [200, '--red: #d33633'],
    '/assets/js/site.js' => [200, "document.querySelectorAll('[data-slider]')"],
    '/sitemap.xml' => [200, '<urlset'],
    '/robots.txt' => [200, 'Disallow: /admin/'],
] as $path => [$expectedStatus, $needle]) {
    $response = $client->request('GET', $path);
    check($response['status'] === $expectedStatus && str_contains($response['body'], $needle), "GET {$path}", $failures);
}

$home = $client->request('GET', '/');
foreach (['X-Content-Type-Options: nosniff', 'X-Frame-Options: SAMEORIGIN', 'Content-Security-Policy:'] as $header) {
    check(str_contains($home['headers'], $header), "security header {$header}", $failures);
}
check(str_contains($home['body'], 'data-slider') && str_contains($home['body'], 'data-theme-toggle'), 'hero slider and theme controls are rendered', $failures);
$aboutSeo = $client->request('GET', '/about');
check(str_contains($aboutSeo['body'], 'BreadcrumbList') && str_contains($aboutSeo['body'], 'hreflang="ar"') && str_contains($aboutSeo['body'], 'hreflang="en"'), 'bilingual SEO and breadcrumb schema', $failures);

$csrfPage = $client->request('POST', '/contact', ['name' => 'Missing CSRF']);
check($csrfPage['status'] === 302, 'contact rejects missing CSRF token', $failures);
$csrfFeedback = $client->request('GET', '/contact');
check(str_contains($csrfFeedback['body'], 'انتهت صلاحية الجلسة'), 'contact reports CSRF failure', $failures);

$contact = $client->request('GET', '/contact');
$invalid = $client->request('POST', '/contact', [
    '_token' => csrf($contact['body']), 'name' => 'QA', 'email' => 'not-an-email',
]);
check($invalid['status'] === 302 && str_ends_with($invalid['location'], '/contact'), 'contact rejects invalid payload', $failures);
$invalidPage = $client->request('GET', '/contact');
check(str_contains($invalidPage['body'], 'not-an-email'), 'contact preserves validly submitted fields', $failures);

$contact = $client->request('GET', '/contact');
$valid = $client->request('POST', '/contact', [
    '_token' => csrf($contact['body']),
    'name' => 'QA Contact ' . $qaSuffix,
    'company' => 'QA Company',
    'email' => "qa-{$qaSuffix}@example.test",
    'phone' => '+9647000000000',
    'country' => 'العراق',
    'request_type' => 'استفسار عام',
    'service' => 'الخدمات النفطية',
    'subject' => 'اختبار نموذج التواصل ' . $qaSuffix,
    'message' => 'هذه رسالة اختبار وظيفي آلية للتحقق من حفظ نموذج التواصل بصورة صحيحة.',
    'privacy' => '1',
]);
check($valid['status'] === 302 && str_ends_with($valid['location'], '/contact'), 'contact accepts valid payload', $failures);
$successPage = $client->request('GET', '/contact');
check(str_contains($successPage['body'], 'تم استلام رسالتك بنجاح'), 'contact shows localized success feedback', $failures);

$loginResponse = login($client, $adminEmail, $adminPassword);
check($loginResponse['status'] === 302 && str_ends_with($loginResponse['location'], '/admin'), 'administrator login', $failures);
$dashboard = $client->request('GET', '/admin');
check($dashboard['status'] === 200 && str_contains($dashboard['body'], 'نظرة عامة'), 'administrator dashboard', $failures);

$serviceCreate = $client->request('GET', '/admin/services/create');
$serviceTitle = 'خدمة اختبار QA مؤقتة ' . $qaSuffix;
$createResponse = $client->request('POST', '/admin/services/create', [
    '_token' => csrf($serviceCreate['body']),
    'title_ar' => $serviceTitle,
    'title_en' => 'Temporary QA service ' . $qaSuffix,
    'slug' => 'qa-service-' . $qaSuffix,
    'summary_ar' => 'محتوى مؤقت للاختبار الوظيفي فقط.',
    'summary_en' => 'Temporary content used only by the functional test.',
    'image_path' => '/assets/images/industrial-supply.webp',
    'status' => 'draft',
    'sort_order' => '99',
    'seo_title_ar' => 'اختبار SEO',
    'seo_title_en' => 'SEO test',
    'seo_description_ar' => 'وصف مؤقت',
    'seo_description_en' => 'Temporary description',
    'og_image' => '',
]);
check($createResponse['status'] === 302 && str_ends_with($createResponse['location'], '/admin/services'), 'CMS create content', $failures);
$serviceList = $client->request('GET', '/admin/services?q=' . rawurlencode($qaSuffix));
$serviceRow = adminRow($serviceList['body'], $serviceTitle);
check($serviceRow !== null && $serviceRow['id'] > 0, 'CMS created content is searchable', $failures);

if ($serviceRow !== null) {
    $editPage = $client->request('GET', $serviceRow['edit']);
    $updatedTitle = 'خدمة اختبار QA معدلة ' . $qaSuffix;
    $update = $client->request('POST', $serviceRow['edit'], [
        '_token' => csrf($editPage['body']),
        'title_ar' => $updatedTitle,
        'title_en' => 'Updated temporary QA service ' . $qaSuffix,
        'slug' => 'qa-service-' . $qaSuffix,
        'summary_ar' => 'تم تعديل المحتوى المؤقت بنجاح.',
        'summary_en' => 'Temporary content was updated successfully.',
        'image_path' => '/assets/images/industrial-supply.webp',
        'status' => 'draft',
        'sort_order' => '98',
        'seo_title_ar' => 'اختبار SEO معدل',
        'seo_title_en' => 'Updated SEO test',
        'seo_description_ar' => 'وصف مؤقت معدل',
        'seo_description_en' => 'Updated temporary description',
        'og_image' => '',
    ]);
    check($update['status'] === 302, 'CMS update content', $failures);
    $updatedList = $client->request('GET', '/admin/services?q=' . rawurlencode($qaSuffix));
    $updatedRow = adminRow($updatedList['body'], $updatedTitle);
    check($updatedRow !== null, 'CMS displays updated content', $failures);
    if ($updatedRow !== null) {
        $toggle = $client->request('POST', $updatedRow['toggle'], ['_token' => csrf($updatedList['body'])]);
        check($toggle['status'] === 302, 'CMS publish/hide toggle', $failures);
        $preview = $client->request('GET', '/admin/services/' . $updatedRow['id'] . '/preview');
        check($preview['status'] === 200 && str_contains($preview['body'], 'تم تعديل المحتوى'), 'CMS preview', $failures);
        $afterToggle = $client->request('GET', '/admin/services?q=' . rawurlencode($qaSuffix));
        $delete = $client->request('POST', $updatedRow['delete'], ['_token' => csrf($afterToggle['body'])]);
        check($delete['status'] === 302, 'CMS soft delete content', $failures);
        $deletedList = $client->request('GET', '/admin/services?q=' . rawurlencode($qaSuffix));
        check(adminRow($deletedList['body'], $updatedTitle) === null, 'CMS hides soft-deleted content', $failures);
    }
}

$pngPath = tempnam(sys_get_temp_dir(), 'maha-qa-png-');
$phpPath = tempnam(sys_get_temp_dir(), 'maha-qa-php-');
if ($pngPath === false || $phpPath === false) {
    throw new RuntimeException('Unable to create upload test files.');
}
file_put_contents($pngPath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true));
file_put_contents($phpPath, '<?php echo "blocked";');
$mediaPage = $client->request('GET', '/admin/media');
$upload = $client->request('POST', '/admin/media/upload', [
    '_token' => csrf($mediaPage['body']),
    'alt_ar' => 'صورة اختبار مؤقتة',
    'alt_en' => 'Temporary QA image',
    'file' => new CURLFile($pngPath, 'image/png', 'qa-upload-' . $qaSuffix . '.png'),
], true);
check($upload['status'] === 302, 'media accepts allowed PNG', $failures);
$uploadedList = $client->request('GET', '/admin/media?q=' . rawurlencode($qaSuffix));
$mediaRow = adminRow($uploadedList['body'], 'qa-upload-' . $qaSuffix . '.png');
check($mediaRow !== null, 'uploaded media appears in library', $failures);
if ($mediaRow !== null) {
    $publicMedia = $client->request('GET', '/media/' . $mediaRow['id']);
    check($publicMedia['status'] === 200 && str_contains($publicMedia['headers'], 'Content-Type: image/png'), 'published media is served through safe public route', $failures);
}

$reject = $client->request('POST', '/admin/media/upload', [
    '_token' => csrf($uploadedList['body']),
    'alt_ar' => '', 'alt_en' => '',
    'file' => new CURLFile($phpPath, 'application/x-php', 'malicious-' . $qaSuffix . '.php'),
], true);
check($reject['status'] === 302, 'media redirects after rejected executable', $failures);
$rejectPage = $client->request('GET', '/admin/media');
check(str_contains($rejectPage['body'], 'غير مسموح') || str_contains($rejectPage['body'], 'تعذر رفع'), 'media reports rejected executable', $failures);
if ($mediaRow !== null) {
    $cleanMediaPage = $client->request('GET', '/admin/media?q=' . rawurlencode($qaSuffix));
    $cleanMediaRow = adminRow($cleanMediaPage['body'], 'qa-upload-' . $qaSuffix . '.png');
    if ($cleanMediaRow !== null) {
        $client->request('POST', $cleanMediaRow['delete'], ['_token' => csrf($cleanMediaPage['body'])]);
    }
}
unlink($pngPath);
unlink($phpPath);

$editorEmail = 'qa-editor-' . $qaSuffix . '@example.test';
$editorPassword = 'Qa!' . bin2hex(random_bytes(10));
$userCreate = $client->request('GET', '/admin/users/create');
$createEditor = $client->request('POST', '/admin/users/create', [
    '_token' => csrf($userCreate['body']),
    'name' => 'QA Editor ' . $qaSuffix,
    'email' => $editorEmail,
    'password' => $editorPassword,
    'role_id' => '2',
    'is_active' => '1',
]);
check($createEditor['status'] === 302, 'CMS creates editor account', $failures);
$usersList = $client->request('GET', '/admin/users?q=' . rawurlencode($qaSuffix));
$editorRow = adminRow($usersList['body'], 'QA Editor ' . $qaSuffix);
check($editorRow !== null, 'editor account appears in user management', $failures);

$logout = $client->request('POST', '/admin/logout', ['_token' => csrf($usersList['body'])]);
check($logout['status'] === 302, 'administrator logout', $failures);
$editorLogin = login($client, $editorEmail, $editorPassword);
check($editorLogin['status'] === 302, 'editor login', $failures);
$editorPages = $client->request('GET', '/admin/pages');
$editorUsers = $client->request('GET', '/admin/users');
$editorSettings = $client->request('GET', '/admin/settings');
check($editorPages['status'] === 200, 'editor can manage content', $failures);
check($editorUsers['status'] === 403, 'editor cannot manage users', $failures);
check($editorSettings['status'] === 403, 'editor cannot manage site settings', $failures);

$editorDashboard = $client->request('GET', '/admin');
$client->request('POST', '/admin/logout', ['_token' => csrf($editorDashboard['body'])]);
login($client, $adminEmail, $adminPassword);
if ($editorRow !== null) {
    $usersList = $client->request('GET', '/admin/users?q=' . rawurlencode($qaSuffix));
    $row = adminRow($usersList['body'], 'QA Editor ' . $qaSuffix);
    if ($row !== null) {
        $client->request('POST', $row['delete'], ['_token' => csrf($usersList['body'])]);
    }
}

$adminPage = $client->request('GET', '/admin');
$client->request('POST', '/admin/logout', ['_token' => csrf($adminPage['body'])]);
$protected = $client->request('GET', '/admin');
check($protected['status'] === 302 && str_ends_with($protected['location'], '/admin/login'), 'protected dashboard redirects anonymous user', $failures);

$throttleClient = new QaClient($baseUrl);
for ($attempt = 0; $attempt < 6; $attempt++) {
    login($throttleClient, 'qa-throttle-' . $qaSuffix . '@example.test', 'WrongPassword!2026');
}
$lockedPage = $throttleClient->request('GET', '/admin/login');
check(str_contains($lockedPage['body'], 'محاولات كثيرة'), 'login throttling after repeated failures', $failures);

echo "\n" . count($failures) . " failure(s).\n";
if ($failures) {
    echo implode("\n", array_map(static fn (string $failure): string => '- ' . $failure, $failures)) . "\n";
    exit(1);
}
