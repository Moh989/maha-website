<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use Throwable;

final class Mailer
{
    public function sendContactNotification(array $message): bool
    {
        $to = strtolower(trim((string) env('SMTP_TO', 'info@almaha-group.com')));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Notification recipient address is invalid.');
        }

        $content = ContactNotificationEmailBuilder::build($message);
        $customerEmail = strtolower(trim((string) ($message['email'] ?? '')));
        $replyTo = filter_var($customerEmail, FILTER_VALIDATE_EMAIL) ? $customerEmail : null;

        if ((bool) env('SMTP_ENABLED', false)) {
            return $this->sendViaSmtp($to, (string) $content['subject'], (string) $content['text'], (string) $content['html'], $replyTo);
        }

        return $this->sendViaNativeMail($to, (string) $content['subject'], (string) $content['text'], (string) $content['html'], $replyTo);
    }

    private function sendViaSmtp(string $to, string $subject, string $text, string $html, ?string $replyTo): bool
    {
        $host = trim((string) env('SMTP_HOST', ''));
        $port = (int) env('SMTP_PORT', 587);
        $encryption = strtolower(trim((string) env('SMTP_ENCRYPTION', 'tls')));
        $timeout = min(60, max(5, (int) env('SMTP_TIMEOUT', 15)));
        if ($host === '' || $port < 1 || $port > 65535 || !in_array($encryption, ['', 'none', 'tls', 'ssl'], true)) {
            throw new RuntimeException('SMTP transport configuration is invalid.');
        }

        $user = trim((string) env('SMTP_USER', ''));
        $password = (string) env('SMTP_PASS', '');
        $from = strtolower(trim((string) env('SMTP_FROM', $user)));
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('SMTP sender address is invalid.');
        }

        $transport = $encryption === 'ssl' ? 'ssl://' : '';
        $socket = @stream_socket_client($transport . $host . ':' . $port, $errorNumber, $errorMessage, $timeout);
        if (!is_resource($socket)) {
            throw new RuntimeException("SMTP connection failed: {$errorNumber} {$errorMessage}");
        }

        try {
            stream_set_timeout($socket, $timeout);
            $this->expect($socket, [220]);
            $helloHost = preg_replace('/[^a-z0-9.-]/i', '', (string) ($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
            $this->command($socket, 'EHLO ' . $helloHost, [250]);
            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Unable to enable SMTP TLS.');
                }
                $this->command($socket, 'EHLO ' . $helloHost, [250]);
            }
            if ($user !== '') {
                $this->command($socket, 'AUTH LOGIN', [334]);
                $this->command($socket, base64_encode($user), [334]);
                $this->command($socket, base64_encode($password), [235]);
            }

            $this->command($socket, 'MAIL FROM:<' . $from . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);
            $payload = $this->smtpPayload($from, $to, $subject, $text, $html, $replyTo);
            fwrite($socket, $this->dotStuff($payload) . "\r\n.\r\n");
            $this->expect($socket, [250]);
            @fwrite($socket, "QUIT\r\n");
        } finally {
            fclose($socket);
        }
        return true;
    }

    private function sendViaNativeMail(string $to, string $subject, string $text, string $html, ?string $replyTo): bool
    {
        if (!function_exists('mail')) {
            throw new RuntimeException('Native PHP mail transport is unavailable.');
        }

        $from = strtolower(trim((string) env('SMTP_FROM', '')));
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $siteHost = (string) parse_url((string) config('app.url'), PHP_URL_HOST);
            $siteHost = preg_replace('/[^a-z0-9.-]/i', '', $siteHost) ?: '';
            $from = 'website@' . $siteHost;
        }
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Native mail sender address could not be determined.');
        }

        [$contentHeaders, $body] = $this->multipartContent($text, $html);
        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . $this->encodedWord($this->senderName()) . ' <' . $from . '>',
            'Message-ID: <' . $this->messageId($from) . '>',
            ...$contentHeaders,
        ];
        if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: <' . $replyTo . '>';
        }

        $sent = @mail($to, $this->encodedWord($this->headerValue($subject)), $body, implode("\r\n", $headers));
        if (!$sent) {
            throw new RuntimeException('Native PHP mail transport rejected the notification.');
        }
        return true;
    }

    private function smtpPayload(string $from, string $to, string $subject, string $text, string $html, ?string $replyTo): string
    {
        [$contentHeaders, $body] = $this->multipartContent($text, $html);
        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . $this->encodedWord($this->senderName()) . ' <' . $from . '>',
            'To: <' . $to . '>',
            'Subject: ' . $this->encodedWord($this->headerValue($subject)),
            'Message-ID: <' . $this->messageId($from) . '>',
            ...$contentHeaders,
        ];
        if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: <' . $replyTo . '>';
        }
        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private function multipartContent(string $text, string $html): array
    {
        try {
            $boundary = '=_almaha_' . bin2hex(random_bytes(12));
        } catch (Throwable) {
            $boundary = '=_almaha_' . str_replace('.', '', uniqid('', true));
        }
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];
        $parts = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            rtrim(chunk_split(base64_encode($this->normalizeLineEndings($text)), 76, "\r\n")),
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            rtrim(chunk_split(base64_encode($this->normalizeLineEndings($html)), 76, "\r\n")),
            '--' . $boundary . '--',
        ];
        return [$headers, implode("\r\n", $parts)];
    }

    private function messageId(string $from): string
    {
        $domain = substr(strrchr($from, '@') ?: '@localhost', 1) ?: 'localhost';
        try {
            $id = bin2hex(random_bytes(16));
        } catch (Throwable) {
            $id = str_replace('.', '', uniqid('', true));
        }
        return $id . '@' . $domain;
    }

    private function senderName(): string
    {
        return $this->headerValue((string) env('SMTP_FROM_NAME', 'شركة المها — الموقع الإلكتروني'));
    }

    private function command($socket, string $command, array $codes): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expect($socket, $codes);
    }

    private function expect($socket, array $codes): void
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        $metadata = stream_get_meta_data($socket);
        if (($metadata['timed_out'] ?? false) === true) {
            throw new RuntimeException('SMTP server response timed out.');
        }
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('SMTP error: ' . trim($response));
        }
    }

    private function encodedWord(string $value): string
    {
        if (function_exists('mb_encode_mimeheader')) {
            return mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n", 0);
        }
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function headerValue(string $value): string
    {
        return trim(str_replace(["\r", "\n"], ' ', $value));
    }

    private function normalizeLineEndings(string $value): string
    {
        return preg_replace("/\r\n|\r|\n/", "\r\n", $value) ?? $value;
    }

    private function dotStuff(string $payload): string
    {
        $payload = $this->normalizeLineEndings($payload);
        return preg_replace('/^\./m', '..', $payload) ?? $payload;
    }
}
