<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function contact(array $data): array
    {
        $errors = [];
        $required = ['name', 'email', 'country', 'request_type', 'service', 'subject', 'message', 'privacy'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                $errors[$field] = __('field_required');
            }
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = __('invalid_email');
        }
        $requestTypes = locale() === 'ar'
            ? ['طلب عرض', 'فرصة تعاون', 'استفسار فني', 'استفسار عام']
            : ['Request for quotation', 'Cooperation opportunity', 'Technical inquiry', 'General inquiry'];
        $services = locale() === 'ar'
            ? ['الخدمات النفطية', 'الأسفلت المؤكسد', 'زيوت التزييت', 'التجهيزات النفطية', 'الخدمات العامة']
            : ['Oil services', 'Oxidized asphalt', 'Lubricating oils', 'Petroleum supplies', 'General services'];
        if (!empty($data['request_type']) && !in_array($data['request_type'], $requestTypes, true)) {
            $errors['request_type'] = __('invalid_selection');
        }
        if (!empty($data['service']) && !in_array($data['service'], $services, true)) {
            $errors['service'] = __('invalid_selection');
        }
        if (isset($data['privacy']) && (string) $data['privacy'] !== '1') {
            $errors['privacy'] = __('field_required');
        }
        foreach (['name' => 120, 'company' => 160, 'country' => 100, 'subject' => 200] as $field => $max) {
            if (mb_strlen((string) ($data[$field] ?? '')) > $max) {
                $errors[$field] = __('field_too_long');
            }
        }
        $length = mb_strlen((string) ($data['message'] ?? ''));
        if ($length > 0 && ($length < 20 || $length > 5000)) {
            $errors['message'] = __('message_length');
        }
        return $errors;
    }
}
