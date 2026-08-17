<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Activity;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

final class AuthController
{
    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/admin');
        }
        set_locale('ar');
        View::render('admin/login', [
            'pageTitle' => 'تسجيل الدخول',
            'error' => Session::pull('error'),
        ], 'layouts/admin-auth');
    }

    public function login(Request $request): void
    {
        if (!Csrf::verify($request->input('_token'))) {
            Session::flash('error', __('csrf_error'));
            Response::redirect('/admin/login');
        }
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');
        if ($email === '' || $password === '' || !Auth::attempt($email, $password, $request)) {
            if (empty($_SESSION['_flash']['error'])) {
                Session::flash('error', 'بيانات الدخول غير صحيحة.');
            }
            Response::redirect('/admin/login');
        }
        Activity::log('login', 'users', (int) Auth::user()['id']);
        Response::redirect('/admin');
    }

    public function logout(Request $request): void
    {
        Auth::requireLogin();
        if (!Csrf::verify($request->input('_token'))) {
            Response::redirect('/admin');
        }
        Activity::log('logout', 'users', (int) Auth::user()['id']);
        Auth::logout();
        Response::redirect('/admin/login');
    }
}
