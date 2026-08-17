<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use PDO;
use Throwable;

final class AdminController
{
    private const RESOURCES = [
        'pages' => [
            'label' => 'الصفحات', 'title' => 'title_ar', 'soft' => true, 'seo' => true,
            'fields' => [
                'title_ar' => ['label' => 'العنوان العربي', 'type' => 'text', 'required' => true],
                'title_en' => ['label' => 'العنوان الإنجليزي', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
                'slug' => ['label' => 'الرابط المختصر', 'type' => 'slug', 'required' => true, 'dir' => 'ltr'],
                'excerpt_ar' => ['label' => 'الملخص العربي', 'type' => 'textarea'],
                'excerpt_en' => ['label' => 'الملخص الإنجليزي', 'type' => 'textarea', 'dir' => 'ltr'],
                'body_ar' => ['label' => 'المحتوى العربي', 'type' => 'textarea-rich'],
                'body_en' => ['label' => 'المحتوى الإنجليزي', 'type' => 'textarea-rich', 'dir' => 'ltr'],
                'template' => ['label' => 'القالب', 'type' => 'select', 'options' => ['content' => 'محتوى', 'services' => 'خدمات', 'projects' => 'مشاريع', 'contact' => 'اتصال', 'profile' => 'ملف تعريفي', 'gallery' => 'معرض']],
                'hero_image' => ['label' => 'مسار صورة الغلاف', 'type' => 'text', 'dir' => 'ltr'],
                'show_in_nav' => ['label' => 'إظهار في القائمة', 'type' => 'checkbox'],
                'status' => ['label' => 'الحالة', 'type' => 'status'],
                'sort_order' => ['label' => 'الترتيب', 'type' => 'number'],
            ],
        ],
        'services' => [
            'label' => 'الخدمات', 'title' => 'title_ar', 'soft' => true, 'seo' => true,
            'fields' => self::CONTENT_FIELDS,
        ],
        'products' => [
            'label' => 'المنتجات', 'title' => 'title_ar', 'soft' => true, 'seo' => true,
            'fields' => self::CONTENT_FIELDS,
        ],
        'projects' => [
            'label' => 'الأعمال السابقة', 'title' => 'title_ar', 'soft' => true, 'seo' => true,
            'fields' => [
                'title_ar' => ['label' => 'العنوان العربي', 'type' => 'text', 'required' => true],
                'title_en' => ['label' => 'العنوان الإنجليزي', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
                'summary_ar' => ['label' => 'الوصف العربي', 'type' => 'textarea-rich'],
                'summary_en' => ['label' => 'الوصف الإنجليزي', 'type' => 'textarea-rich', 'dir' => 'ltr'],
                'contract_date' => ['label' => 'تاريخ العقد', 'type' => 'date'],
                'image_path' => ['label' => 'مسار الصورة', 'type' => 'text', 'dir' => 'ltr'],
                'status' => ['label' => 'الحالة', 'type' => 'status'], 'sort_order' => ['label' => 'الترتيب', 'type' => 'number'],
            ],
        ],
        'slider_items' => [
            'label' => 'شرائح الواجهة', 'title' => 'title_ar', 'soft' => true, 'seo' => false,
            'fields' => [
                'title_ar' => ['label' => 'العنوان العربي', 'type' => 'text', 'required' => true],
                'title_en' => ['label' => 'العنوان الإنجليزي', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
                'subtitle_ar' => ['label' => 'النص العربي', 'type' => 'textarea'],
                'subtitle_en' => ['label' => 'النص الإنجليزي', 'type' => 'textarea', 'dir' => 'ltr'],
                'image_path' => ['label' => 'مسار الصورة', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
                'cta_url' => ['label' => 'رابط الزر', 'type' => 'text', 'dir' => 'ltr'],
                'status' => ['label' => 'الحالة', 'type' => 'status'], 'sort_order' => ['label' => 'الترتيب', 'type' => 'number'],
            ],
        ],
        'news' => [
            'label' => 'الأخبار', 'title' => 'title_ar', 'soft' => true, 'seo' => true,
            'fields' => self::CONTENT_FIELDS,
        ],
        'faqs' => [
            'label' => 'الأسئلة الشائعة', 'title' => 'question_ar', 'soft' => true, 'seo' => false,
            'fields' => [
                'question_ar' => ['label' => 'السؤال العربي', 'type' => 'text', 'required' => true],
                'question_en' => ['label' => 'السؤال الإنجليزي', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
                'answer_ar' => ['label' => 'الإجابة العربية', 'type' => 'textarea-rich'],
                'answer_en' => ['label' => 'الإجابة الإنجليزية', 'type' => 'textarea-rich', 'dir' => 'ltr'],
                'status' => ['label' => 'الحالة', 'type' => 'status'], 'sort_order' => ['label' => 'الترتيب', 'type' => 'number'],
            ],
        ],
        'media' => [
            'label' => 'الوسائط', 'title' => 'original_name', 'soft' => true, 'seo' => false,
            'fields' => [
                'alt_ar' => ['label' => 'النص البديل العربي', 'type' => 'text'],
                'alt_en' => ['label' => 'النص البديل الإنجليزي', 'type' => 'text', 'dir' => 'ltr'],
                'status' => ['label' => 'الحالة', 'type' => 'status'], 'sort_order' => ['label' => 'الترتيب', 'type' => 'number'],
            ],
        ],
        'settings' => [
            'label' => 'الإعدادات', 'title' => 'setting_key', 'soft' => false, 'seo' => false, 'admin_only' => true,
            'fields' => [
                'setting_key' => ['label' => 'المفتاح', 'type' => 'slug', 'required' => true, 'dir' => 'ltr'],
                'setting_value' => ['label' => 'القيمة', 'type' => 'textarea'],
                'setting_group' => ['label' => 'المجموعة', 'type' => 'text', 'dir' => 'ltr'],
            ],
        ],
        'users' => [
            'label' => 'المستخدمون', 'title' => 'name', 'soft' => true, 'seo' => false, 'admin_only' => true,
            'fields' => [
                'name' => ['label' => 'الاسم', 'type' => 'text', 'required' => true],
                'email' => ['label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'dir' => 'ltr'],
                'password' => ['label' => 'كلمة المرور', 'type' => 'password'],
                'role_id' => ['label' => 'الصلاحية', 'type' => 'roles', 'required' => true],
                'is_active' => ['label' => 'حساب فعال', 'type' => 'checkbox'],
            ],
        ],
        'contact_messages' => [
            'label' => 'رسائل التواصل', 'title' => 'subject', 'soft' => true, 'seo' => false, 'no_create' => true,
            'fields' => [
                'name' => ['label' => 'الاسم', 'type' => 'readonly'], 'company' => ['label' => 'الشركة', 'type' => 'readonly'],
                'email' => ['label' => 'البريد', 'type' => 'readonly'], 'phone' => ['label' => 'الهاتف', 'type' => 'readonly'],
                'country' => ['label' => 'الدولة', 'type' => 'readonly'], 'request_type' => ['label' => 'نوع الطلب', 'type' => 'readonly'],
                'service' => ['label' => 'الخدمة', 'type' => 'readonly'], 'subject' => ['label' => 'العنوان', 'type' => 'readonly'],
                'message' => ['label' => 'الرسالة', 'type' => 'readonly-textarea'],
                'status' => ['label' => 'الحالة', 'type' => 'select', 'options' => ['new' => 'جديدة', 'read' => 'مقروءة', 'archived' => 'مؤرشفة']],
            ],
        ],
        'activity_logs' => [
            'label' => 'سجل العمليات', 'title' => 'action', 'soft' => false, 'seo' => false, 'no_create' => true, 'read_only' => true, 'admin_only' => true,
            'fields' => [],
        ],
    ];

    private const CONTENT_FIELDS = [
        'title_ar' => ['label' => 'العنوان العربي', 'type' => 'text', 'required' => true],
        'title_en' => ['label' => 'العنوان الإنجليزي', 'type' => 'text', 'required' => true, 'dir' => 'ltr'],
        'slug' => ['label' => 'الرابط المختصر', 'type' => 'slug', 'required' => true, 'dir' => 'ltr'],
        'summary_ar' => ['label' => 'الملخص العربي', 'type' => 'textarea-rich'],
        'summary_en' => ['label' => 'الملخص الإنجليزي', 'type' => 'textarea-rich', 'dir' => 'ltr'],
        'image_path' => ['label' => 'مسار الصورة', 'type' => 'text', 'dir' => 'ltr'],
        'status' => ['label' => 'الحالة', 'type' => 'status'],
        'sort_order' => ['label' => 'الترتيب', 'type' => 'number'],
    ];

    public function dashboard(Request $request): void
    {
        Auth::requireLogin();
        $pdo = Database::connection();
        $counts = [];
        foreach (['pages', 'services', 'products', 'projects', 'news', 'contact_messages'] as $table) {
            $counts[$table] = (int) $pdo->query("SELECT COUNT(*) FROM {$table} WHERE deleted_at IS NULL")->fetchColumn();
        }
        $recent = $pdo->query('SELECT l.*, u.name AS user_name FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.id DESC LIMIT 12')->fetchAll();
        View::render('admin/dashboard', $this->shared([
            'pageTitle' => 'نظرة عامة', 'counts' => $counts, 'recent' => $recent,
        ]), 'layouts/admin');
    }

    public function index(Request $request, string $resource): void
    {
        $config = $this->authorizeResource($resource);
        $pdo = Database::connection();
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];
        if (($config['soft'] ?? false) === true) {
            $where[] = 'deleted_at IS NULL';
        }
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $where[] = "CAST({$config['title']} AS CHAR) LIKE :q";
            $params['q'] = '%' . $q . '%';
        }
        $status = trim((string) $request->input('status', ''));
        if ($status !== '' && $this->hasField($config, 'status')) {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }
        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $count = $pdo->prepare("SELECT COUNT(*) FROM {$resource}{$whereSql}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $statement = $pdo->prepare("SELECT * FROM {$resource}{$whereSql} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
        $statement->execute($params);
        View::render('admin/index', $this->shared([
            'pageTitle' => $config['label'], 'resource' => $resource, 'resourceConfig' => $config,
            'items' => $statement->fetchAll(), 'page' => $page, 'pages' => max(1, (int) ceil($total / $perPage)),
            'q' => $q, 'status' => $status,
        ]), 'layouts/admin');
    }

    public function create(Request $request, string $resource): void
    {
        $config = $this->authorizeResource($resource);
        if (($config['no_create'] ?? false) || ($config['read_only'] ?? false)) {
            Response::redirect('/admin/' . $resource);
        }
        View::render('admin/form', $this->shared([
            'pageTitle' => 'إضافة - ' . $config['label'], 'resource' => $resource, 'resourceConfig' => $config,
            'item' => [], 'roles' => $this->roles(), 'seo' => [],
        ]), 'layouts/admin');
    }

    public function store(Request $request, string $resource): void
    {
        $config = $this->authorizeResource($resource);
        if (($config['no_create'] ?? false) || ($config['read_only'] ?? false)) {
            Response::redirect('/admin/' . $resource);
        }
        $this->verifyCsrf('/admin/' . $resource . '/create');
        $data = $this->validatedData($request, $resource, $config, null);
        if ($data === null) {
            Response::redirect('/admin/' . $resource . '/create');
        }
        try {
            $id = $this->insert($resource, $config, $data);
            $this->saveSeo($resource, $id, $request, $config);
            Activity::log('create', $resource, $id, ['title' => $data[$config['title']] ?? '']);
            Session::flash('success', 'تمت الإضافة بنجاح.');
        } catch (Throwable $exception) {
            error_log('Admin create failed: ' . $exception->getMessage());
            Session::flash('error', 'تعذر الحفظ. تحقق من أن الرابط أو البريد غير مستخدم مسبقاً.');
        }
        Response::redirect('/admin/' . $resource);
    }

    public function edit(Request $request, string $resource, string $id): void
    {
        $config = $this->authorizeResource($resource);
        if (($config['read_only'] ?? false)) {
            Response::redirect('/admin/' . $resource);
        }
        $item = $this->find($resource, (int) $id, $config);
        if (!$item) {
            Response::redirect('/admin/' . $resource);
        }
        View::render('admin/form', $this->shared([
            'pageTitle' => 'تعديل - ' . $config['label'], 'resource' => $resource, 'resourceConfig' => $config,
            'item' => $item, 'roles' => $this->roles(), 'seo' => $this->seo($resource, (int) $id),
        ]), 'layouts/admin');
    }

    public function update(Request $request, string $resource, string $id): void
    {
        $config = $this->authorizeResource($resource);
        if (($config['read_only'] ?? false)) {
            Response::redirect('/admin/' . $resource);
        }
        $this->verifyCsrf('/admin/' . $resource . '/' . $id . '/edit');
        $data = $this->validatedData($request, $resource, $config, (int) $id);
        if ($data === null) {
            Response::redirect('/admin/' . $resource . '/' . $id . '/edit');
        }
        try {
            $this->performUpdate($resource, (int) $id, $config, $data);
            $this->saveSeo($resource, (int) $id, $request, $config);
            Activity::log('update', $resource, (int) $id, ['title' => $data[$config['title']] ?? '']);
            Session::flash('success', 'تم حفظ التعديلات.');
        } catch (Throwable $exception) {
            error_log('Admin update failed: ' . $exception->getMessage());
            Session::flash('error', 'تعذر حفظ التعديلات.');
        }
        Response::redirect('/admin/' . $resource);
    }

    public function delete(Request $request, string $resource, string $id): void
    {
        $config = $this->authorizeResource($resource);
        $this->verifyCsrf('/admin/' . $resource);
        if (($config['read_only'] ?? false)) {
            Response::redirect('/admin/' . $resource);
        }
        if ($resource === 'users' && (int) $id === (int) Auth::user()['id']) {
            Session::flash('error', 'لا يمكن حذف الحساب المستخدم حالياً.');
            Response::redirect('/admin/users');
        }
        if (($config['soft'] ?? false) === true) {
            Database::connection()->prepare("UPDATE {$resource} SET deleted_at = NOW() WHERE id = :id")->execute(['id' => (int) $id]);
        } else {
            Database::connection()->prepare("DELETE FROM {$resource} WHERE id = :id")->execute(['id' => (int) $id]);
        }
        Activity::log('delete', $resource, (int) $id);
        Session::flash('success', 'تم الحذف.');
        Response::redirect('/admin/' . $resource);
    }

    public function toggle(Request $request, string $resource, string $id): void
    {
        $config = $this->authorizeResource($resource);
        $this->verifyCsrf('/admin/' . $resource);
        if (!$this->hasField($config, 'status') || ($config['read_only'] ?? false)) {
            Response::redirect('/admin/' . $resource);
        }
        $statement = Database::connection()->prepare("UPDATE {$resource} SET status = CASE WHEN status = 'published' THEN 'hidden' ELSE 'published' END, updated_at = NOW() WHERE id = :id");
        $statement->execute(['id' => (int) $id]);
        Activity::log('toggle_status', $resource, (int) $id);
        Session::flash('success', 'تم تحديث حالة النشر.');
        Response::redirect('/admin/' . $resource);
    }

    public function preview(Request $request, string $resource, string $id): void
    {
        $config = $this->authorizeResource($resource);
        $item = $this->find($resource, (int) $id, $config);
        if (!$item) {
            Response::redirect('/admin/' . $resource);
        }
        View::render('admin/preview', [
            'pageTitle' => 'معاينة', 'resource' => $resource, 'item' => $item, 'resourceConfig' => $config,
        ], 'layouts/admin-preview');
    }

    private function authorizeResource(string $resource): array
    {
        Auth::requireLogin();
        if (!isset(self::RESOURCES[$resource])) {
            http_response_code(404);
            exit;
        }
        $config = self::RESOURCES[$resource];
        if (($config['admin_only'] ?? false) && Auth::user()['role_slug'] !== 'administrator') {
            Auth::requireRole('administrator');
        }
        return $config;
    }

    private function validatedData(Request $request, string $resource, array $config, ?int $id): ?array
    {
        $data = [];
        $errors = [];
        foreach ($config['fields'] as $field => $fieldConfig) {
            if (str_starts_with($field, 'seo_') || str_starts_with($fieldConfig['type'], 'readonly')) {
                continue;
            }
            if ($fieldConfig['type'] === 'password') {
                $value = (string) $request->input($field, '');
                if ($id === null && $value === '') {
                    $errors[$field] = 'كلمة المرور مطلوبة.';
                }
                if ($value !== '' && strlen($value) < 12) {
                    $errors[$field] = 'كلمة المرور يجب ألا تقل عن 12 حرفاً.';
                }
                if ($value !== '') {
                    $data['password_hash'] = password_hash($value, PASSWORD_DEFAULT);
                }
                continue;
            }
            $value = $fieldConfig['type'] === 'checkbox' ? ($request->input($field) ? 1 : 0) : trim((string) $request->input($field, ''));
            if (($fieldConfig['required'] ?? false) && $value === '') {
                $errors[$field] = 'هذا الحقل مطلوب.';
            }
            if ($fieldConfig['type'] === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'البريد الإلكتروني غير صحيح.';
            }
            if ($fieldConfig['type'] === 'slug' && $value !== '' && !preg_match('/^[a-z0-9][a-z0-9_-]*$/', $value)) {
                $errors[$field] = 'استخدم أحرفاً إنجليزية صغيرة وأرقاماً وشرطة فقط.';
            }
            $data[$field] = $value === '' && in_array($fieldConfig['type'], ['date'], true) ? null : $value;
        }
        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $request->all());
            return null;
        }
        return $data;
    }

    private function insert(string $resource, array $config, array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $fields = array_keys($data);
        $sql = "INSERT INTO {$resource} (" . implode(', ', $fields) . ') VALUES (:' . implode(', :', $fields) . ')';
        $statement = Database::connection()->prepare($sql);
        $statement->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    private function performUpdate(string $resource, int $id, array $config, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $assignments = array_map(static fn ($field) => "{$field} = :{$field}", array_keys($data));
        $data['id'] = $id;
        $statement = Database::connection()->prepare("UPDATE {$resource} SET " . implode(', ', $assignments) . ' WHERE id = :id');
        $statement->execute($data);
    }

    private function find(string $resource, int $id, array $config): ?array
    {
        $where = 'id = :id' . (($config['soft'] ?? false) ? ' AND deleted_at IS NULL' : '');
        $statement = Database::connection()->prepare("SELECT * FROM {$resource} WHERE {$where} LIMIT 1");
        $statement->execute(['id' => $id]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function saveSeo(string $resource, int $id, Request $request, array $config): void
    {
        if (!($config['seo'] ?? false)) {
            return;
        }
        $statement = Database::connection()->prepare(
            'INSERT INTO seo_metadata (entity_type, entity_id, title_ar, title_en, description_ar, description_en, og_image, created_at, updated_at)
             VALUES (:type, :id, :title_ar, :title_en, :description_ar, :description_en, :og_image, NOW(), NOW())
             ON DUPLICATE KEY UPDATE title_ar = VALUES(title_ar), title_en = VALUES(title_en), description_ar = VALUES(description_ar), description_en = VALUES(description_en), og_image = VALUES(og_image), updated_at = NOW()'
        );
        $statement->execute([
            'type' => $resource, 'id' => $id,
            'title_ar' => trim((string) $request->input('seo_title_ar')), 'title_en' => trim((string) $request->input('seo_title_en')),
            'description_ar' => trim((string) $request->input('seo_description_ar')), 'description_en' => trim((string) $request->input('seo_description_en')),
            'og_image' => trim((string) $request->input('og_image')) ?: null,
        ]);
    }

    private function seo(string $resource, int $id): array
    {
        $statement = Database::connection()->prepare('SELECT * FROM seo_metadata WHERE entity_type = :type AND entity_id = :id LIMIT 1');
        $statement->execute(['type' => $resource, 'id' => $id]);
        return $statement->fetch() ?: [];
    }

    private function roles(): array
    {
        return Database::connection()->query('SELECT id, name_ar FROM roles ORDER BY id')->fetchAll();
    }

    private function hasField(array $config, string $field): bool
    {
        return isset($config['fields'][$field]);
    }

    private function verifyCsrf(string $return): void
    {
        if (!Csrf::verify($_POST['_token'] ?? null)) {
            Session::flash('error', __('csrf_error'));
            Response::redirect($return);
        }
    }

    private function shared(array $data): array
    {
        return array_merge([
            'user' => Auth::user(), 'resources' => self::RESOURCES,
            'flashSuccess' => Session::pull('success'), 'flashError' => Session::pull('error'),
            'errors' => Session::pull('errors', []), 'old' => Session::pull('old', []),
        ], $data);
    }
}
