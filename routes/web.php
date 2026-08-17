<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\MediaController;
use App\Controllers\SiteController;

$router->get('/', [SiteController::class, 'home']);
$router->get('/en', static fn ($request) => (new SiteController())->home($request, 'en'));
$router->get('/lang/{locale}', [SiteController::class, 'language']);
$router->get('/sitemap.xml', [SiteController::class, 'sitemap']);
$router->get('/robots.txt', [SiteController::class, 'robots']);
$router->get('/media/{id}', [SiteController::class, 'media']);

$router->post('/contact', static fn ($request) => (new SiteController())->contactSubmit($request, 'ar'));
$router->post('/en/contact', static fn ($request) => (new SiteController())->contactSubmit($request, 'en'));

$router->get('/admin/login', [AuthController::class, 'showLogin']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/logout', [AuthController::class, 'logout']);
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/error-logs', [AdminController::class, 'errorLogs']);
$router->get('/admin/media/file/{id}', [MediaController::class, 'serve']);
$router->post('/admin/media/upload', [MediaController::class, 'upload']);
$router->get('/admin/{resource}', [AdminController::class, 'index']);
$router->get('/admin/{resource}/create', [AdminController::class, 'create']);
$router->post('/admin/{resource}/create', [AdminController::class, 'store']);
$router->get('/admin/{resource}/{id}/edit', [AdminController::class, 'edit']);
$router->post('/admin/{resource}/{id}/edit', [AdminController::class, 'update']);
$router->post('/admin/{resource}/{id}/delete', [AdminController::class, 'delete']);
$router->post('/admin/{resource}/{id}/toggle', [AdminController::class, 'toggle']);
$router->get('/admin/{resource}/{id}/preview', [AdminController::class, 'preview']);

$router->get('/en/{slug}', static fn ($request, $slug) => (new SiteController())->page($request, $slug, 'en'));
$router->get('/{slug}', static fn ($request, $slug) => (new SiteController())->page($request, $slug, 'ar'));
