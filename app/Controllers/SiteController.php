<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Logger;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\ContentRepository;
use App\Services\Mailer;
use Throwable;

final class SiteController
{
    private ContentRepository $content;

    public function __construct()
    {
        $this->content = new ContentRepository();
    }

    public function language(Request $request, string $locale): void
    {
        set_locale($locale === 'en' ? 'en' : 'ar');
        $target = $request->input('return', '/');
        if (!is_string($target) || !str_starts_with($target, '/') || str_starts_with($target, '//')) {
            $target = '/';
        }
        Response::redirect($target);
    }

    public function home(Request $request, ?string $lang = null): void
    {
        $this->applyLanguage($lang);
        View::render('pages/home', $this->shared([
            'pageTitle' => locale() === 'ar' ? 'شركة المها للخدمات النفطية' : 'Al-Maha Oil Services',
            'pageDescription' => locale() === 'ar'
                ? 'شركة عراقية خاصة في الخدمات النفطية وإنتاج الأسفلت المؤكسد وزيوت التزييت وتجهيز المواد والخدمات العامة.'
                : 'A private Iraqi company serving oil services, oxidized asphalt, lubricating oils, petroleum supplies and general services.',
            'sliders' => $this->content->all('slider_items', 6),
            'services' => $this->content->all('services', 8),
            'products' => $this->content->all('products', 8),
            'projects' => $this->content->all('projects', 3),
        ]));
    }

    public function page(Request $request, string $slug, ?string $lang = null): void
    {
        $this->applyLanguage($lang);
        $page = $this->content->page($slug);
        if (!$page) {
            http_response_code(404);
            View::render('pages/404', $this->shared(['pageTitle' => __('not_found')]));
            return;
        }
        $view = match ($page['template']) {
            'contact' => 'pages/contact',
            'profile' => 'pages/profile',
            'projects' => 'pages/projects',
            'services' => 'pages/services',
            'gallery' => 'pages/gallery',
            default => 'pages/content',
        };
        $data = [
            'page' => $page,
            'pageTitle' => localized_field($page, 'seo_title') ?: localized_field($page, 'title'),
            'pageDescription' => localized_field($page, 'seo_description') ?: localized_field($page, 'excerpt'),
        ];
        if ($page['template'] === 'projects') {
            $data['projects'] = $this->content->all('projects', 50);
        }
        if ($page['template'] === 'services') {
            $data['services'] = $this->content->all('services', 50);
        }
        if ($page['template'] === 'gallery') {
            $data['media'] = $this->content->all('media', 50);
        }
        View::render($view, $this->shared($data));
    }

    public function contactSubmit(Request $request, ?string $lang = null): void
    {
        $this->applyLanguage($lang);
        $return = locale() === 'en' ? '/en/contact' : '/contact';
        $data = array_map(static fn ($value) => is_string($value) ? trim($value) : $value, $request->all());
        unset($data['_token']);

        if (!Csrf::verify($request->input('_token'))) {
            Session::flash('errors', ['form' => __('csrf_error')]);
            Session::flash('old', $data);
            Response::redirect($return);
        }
        if (!empty($data['website'])) {
            Session::flash('success', __('contact_success'));
            Response::redirect($return);
        }
        if (RateLimiter::tooMany('contact', $request->ip(), 5, 30)) {
            Session::flash('errors', ['form' => __('rate_limit')]);
            Session::flash('old', $data);
            Response::redirect($return);
        }
        RateLimiter::hit('contact', $request->ip());

        $errors = Validator::contact($data);
        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Response::redirect($return);
        }

        try {
            $statement = Database::connection()->prepare(
                'INSERT INTO contact_messages (name, company, email, country, request_type, service, subject, message, privacy_consent, locale, ip_hash, user_agent, status, created_at, updated_at) VALUES (:name, :company, :email, :country, :request_type, :service, :subject, :message, 1, :locale, :ip_hash, :user_agent, \'new\', NOW(), NOW())'
            );
            $statement->execute([
                'name' => $data['name'], 'company' => ($data['company'] ?? '') !== '' ? $data['company'] : null, 'email' => strtolower($data['email']),
                'country' => $data['country'], 'request_type' => $data['request_type'],
                'service' => $data['service'], 'subject' => $data['subject'], 'message' => $data['message'], 'locale' => locale(),
                'ip_hash' => hash('sha256', $request->ip() . '|' . (string) env('APP_KEY', 'local')),
                'user_agent' => $request->userAgent(),
            ]);
            $data['contact_id'] = (int) Database::connection()->lastInsertId();
            $data['locale'] = locale();
            $mailer = new Mailer();
            try {
                $mailer->sendContactNotification($data);
            } catch (Throwable $mailerException) {
                Logger::exception('CONTACT_NOTIFICATION_EMAIL_FAILED', $mailerException, [
                    'contact_message_id' => $data['contact_id'],
                    'locale' => locale(),
                    'request_type' => $data['request_type'],
                    'service' => $data['service'],
                ]);
            }
            Session::flash('success', __('contact_success'));
        } catch (Throwable $exception) {
            Logger::exception('CONTACT_SUBMISSION_FAILED', $exception, [
                'locale' => locale(),
                'request_type' => $data['request_type'],
                'service' => $data['service'],
            ]);
            Session::flash('errors', ['form' => __('contact_failed')]);
            Session::flash('old', $data);
        }
        Response::redirect($return);
    }

    public function sitemap(Request $request): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $pages = Database::connection()->query("SELECT slug, updated_at FROM pages WHERE status = 'published' AND deleted_at IS NULL ORDER BY sort_order")->fetchAll();
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        echo '<url><loc>' . e(url('/')) . '</loc><xhtml:link rel="alternate" hreflang="ar" href="' . e(url('/')) . '"/><xhtml:link rel="alternate" hreflang="en" href="' . e(url('/en')) . '"/></url>';
        foreach ($pages as $page) {
            foreach (['ar' => '', 'en' => '/en'] as $lang => $prefix) {
                $loc = url($prefix . '/' . $page['slug']);
                echo '<url><loc>' . e($loc) . '</loc><lastmod>' . e(date('c', strtotime($page['updated_at']))) . '</lastmod></url>';
            }
        }
        echo '</urlset>';
    }

    public function robots(Request $request): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        $basePath = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');
        echo "User-agent: *\n"
            . 'Allow: ' . ($basePath === '' ? '/' : $basePath . '/') . "\n"
            . 'Disallow: ' . $basePath . "/admin/\n"
            . 'Disallow: ' . $basePath . "/storage/\n"
            . 'Sitemap: ' . url('/sitemap.xml') . "\n";
    }

    public function media(Request $request, string $id): void
    {
        $statement = Database::connection()->prepare("SELECT path, mime_type, original_name FROM media WHERE id = :id AND status = 'published' AND deleted_at IS NULL LIMIT 1");
        $statement->execute(['id' => (int) $id]);
        $media = $statement->fetch();
        if (!$media) {
            http_response_code(404);
            exit;
        }
        $root = realpath(base_path('storage/uploads')) ?: '';
        $path = realpath(base_path('storage/uploads/' . $media['path'])) ?: '';
        if ($root === '' || $path === '' || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . $media['mime_type']);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=86400');
        readfile($path);
        exit;
    }

    private function applyLanguage(?string $lang): void
    {
        if ($lang !== null) {
            set_locale($lang === 'en' ? 'en' : 'ar');
        }
    }

    private function shared(array $data): array
    {
        return array_merge([
            'settings' => $this->content->settings(),
            'navigation' => $this->content->navigation(),
            'publishedSlugs' => $this->content->publishedSlugs(),
            'flashSuccess' => Session::pull('success'),
            'errors' => Session::pull('errors', []),
            'old' => Session::pull('old', []),
        ], $data);
    }
}
