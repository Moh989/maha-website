<?php

declare(strict_types=1);

return [
    'name_ar' => 'شركة المها للخدمات النفطية وانتاج الاسفلت المؤكسد وزيت التزييت والخدمات العامة محدودة المسؤولية',
    'name_en' => 'Al-Maha Co. for Oil Services, Production of Oxidized Asphalt & Lubricating Oils and General Services Ltd',
    'default_locale' => 'ar',
    'locales' => ['ar', 'en'],
    'timezone' => env('APP_TIMEZONE', 'Asia/Baghdad'),
    'url' => rtrim((string) env('APP_URL', 'http://localhost:8080'), '/'),
];
