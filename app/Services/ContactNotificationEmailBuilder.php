<?php

declare(strict_types=1);

namespace App\Services;

final class ContactNotificationEmailBuilder
{
    public static function build(array $message): array
    {
        $reference = isset($message['contact_id']) && (int) $message['contact_id'] > 0
            ? '#' . (int) $message['contact_id']
            : '';
        $subjectValue = self::clean((string) ($message['subject'] ?? ''), 200);
        $fields = array_filter([
            'رقم المرجع' => $reference,
            'الاسم' => self::clean((string) ($message['name'] ?? ''), 160),
            'الشركة أو الجهة' => self::clean((string) ($message['company'] ?? ''), 160),
            'البريد الإلكتروني' => self::clean((string) ($message['email'] ?? ''), 190),
            'رقم الهاتف' => self::clean((string) ($message['phone'] ?? ''), 80),
            'الدولة' => self::clean((string) ($message['country'] ?? ''), 100),
            'نوع الطلب' => self::clean((string) ($message['request_type'] ?? ''), 100),
            'الخدمة المطلوبة' => self::clean((string) ($message['service'] ?? ''), 160),
            'عنوان الرسالة' => $subjectValue,
            'لغة النموذج' => ($message['locale'] ?? 'ar') === 'en' ? 'English' : 'العربية',
            'وقت الاستلام' => date('Y-m-d H:i:s'),
        ], static fn (string $value): bool => $value !== '');

        $body = self::cleanMultiline((string) ($message['message'] ?? ''), 5000);
        $subject = 'رسالة جديدة عبر الموقع الإلكتروني'
            . ($subjectValue !== '' ? ' — ' . $subjectValue : '');

        return [
            'subject' => $subject,
            'text' => self::plainText($fields, $body),
            'html' => self::html($fields, $body, $reference),
        ];
    }

    private static function plainText(array $fields, string $body): string
    {
        $lines = [
            'وصلت رسالة جديدة عن طريق الموقع الإلكتروني لشركة المها.',
            '',
        ];
        foreach ($fields as $label => $value) {
            $lines[] = $label . ': ' . $value;
        }
        $lines[] = '';
        $lines[] = 'نص الرسالة:';
        $lines[] = $body;
        if (isset($fields['رقم المرجع'])) {
            $lines[] = '';
            $lines[] = 'فتح الرسالة في لوحة الإدارة: ' . url('/admin/contact_messages/' . ltrim($fields['رقم المرجع'], '#') . '/edit');
        }
        return implode("\r\n", $lines);
    }

    private static function html(array $fields, string $body, string $reference): string
    {
        $escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $rows = '';
        foreach ($fields as $label => $value) {
            $direction = in_array($label, ['البريد الإلكتروني', 'رقم الهاتف', 'رقم المرجع', 'وقت الاستلام'], true) ? 'ltr' : 'rtl';
            $rows .= '<tr><td style="padding:11px 0;border-bottom:1px solid #e5eaed;color:#66747e;font-size:12px;font-weight:700;vertical-align:top;width:34%;">'
                . $escape($label)
                . '</td><td dir="' . $direction . '" style="padding:11px 0;border-bottom:1px solid #e5eaed;color:#101a22;font-size:13px;font-weight:700;vertical-align:top;text-align:right;overflow-wrap:anywhere;">'
                . $escape($value) . '</td></tr>';
        }

        $adminUrl = $reference !== ''
            ? url('/admin/contact_messages/' . ltrim($reference, '#') . '/edit')
            : url('/admin/contact_messages');
        $companyName = htmlspecialchars((string) config('app.name_ar'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>رسالة جديدة من الموقع</title></head>'
            . '<body style="margin:0;padding:0;background:#eef2f4;color:#101a22;font-family:Tahoma,Arial,sans-serif;direction:rtl;text-align:right;">'
            . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">وصلت رسالة جديدة عن طريق الموقع الإلكتروني لشركة المها.</div>'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#eef2f4;"><tr><td align="center" style="padding:34px 14px;">'
            . '<table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:680px;background:#ffffff;border-collapse:separate;border-spacing:0;border-radius:18px;overflow:hidden;box-shadow:0 18px 50px rgba(4,22,37,.10);">'
            . '<tr><td style="height:6px;background:#d33633;font-size:0;line-height:0;">&nbsp;</td></tr>'
            . '<tr><td style="padding:28px 36px;background:#07192c;color:#ffffff;">'
            . '<div style="font-size:18px;font-weight:800;line-height:1.6;">شركة المها</div><div style="margin-top:3px;color:#62c5e7;font-size:10px;font-weight:700;letter-spacing:.08em;direction:ltr;text-align:right;">OIL · ENERGY · INDUSTRY</div>'
            . '</td></tr>'
            . '<tr><td style="padding:38px 42px 18px;">'
            . '<div style="color:#d33633;font-size:11px;font-weight:800;">إشعار من الموقع الإلكتروني</div>'
            . '<h1 style="margin:9px 0 12px;color:#07192c;font-size:27px;line-height:1.5;font-weight:800;">وصلت رسالة جديدة عبر الموقع</h1>'
            . '<p style="margin:0;color:#52616c;font-size:14px;line-height:1.9;">تم حفظ الرسالة في لوحة الإدارة، وتظهر تفاصيل المرسل والطلب أدناه.</p>'
            . '</td></tr>'
            . '<tr><td style="padding:14px 42px;"><div style="padding:22px 24px;background:#f5f8f9;border-right:4px solid #099bcc;border-radius:10px;">'
            . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:collapse;text-align:right;">' . $rows . '</table>'
            . '</div></td></tr>'
            . '<tr><td style="padding:14px 42px 8px;"><div style="padding:20px 22px;background:#ffffff;border:1px solid #dce3e7;border-radius:10px;">'
            . '<div style="margin-bottom:9px;color:#07192c;font-size:13px;font-weight:800;">نص الرسالة</div>'
            . '<div style="color:#3f4e59;font-size:14px;line-height:2;overflow-wrap:anywhere;">' . nl2br($escape($body), false) . '</div>'
            . '</div></td></tr>'
            . '<tr><td style="padding:20px 42px 38px;text-align:center;">'
            . '<a href="' . $escape($adminUrl) . '" style="display:inline-block;padding:13px 24px;color:#ffffff;background:#d33633;border-radius:8px;font-size:13px;font-weight:800;text-decoration:none;">فتح الرسالة في لوحة الإدارة</a>'
            . '<p style="margin:14px 0 0;color:#7a8790;font-size:10px;">يمكن الرد مباشرة على هذا البريد للتواصل مع صاحب الرسالة.</p>'
            . '</td></tr>'
            . '<tr><td style="padding:23px 32px;background:#07192c;color:#9fb1bd;text-align:center;">'
            . '<div style="color:#ffffff;font-size:11px;font-weight:800;line-height:1.8;">' . $companyName . '</div>'
            . '<div style="margin-top:10px;color:#6f8593;font-size:9px;direction:ltr;">' . $escape(url('/')) . '</div>'
            . '</td></tr></table></td></tr></table></body></html>';
    }

    private static function clean(string $value, int $limit): string
    {
        $value = trim(preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value) ?? '');
        return mb_strlen($value) > $limit ? mb_substr($value, 0, $limit) . '…' : $value;
    }

    private static function cleanMultiline(string $value, int $limit): string
    {
        $value = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '');
        return mb_strlen($value) > $limit ? mb_substr($value, 0, $limit) . '…' : $value;
    }
}
