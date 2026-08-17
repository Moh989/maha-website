<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class Mailer
{
    public function sendContactNotification(array $message): bool
    {
        if (!(bool) env('SMTP_ENABLED', false)) {
            return false;
        }

        $host = (string) env('SMTP_HOST', '');
        $port = (int) env('SMTP_PORT', 587);
        $encryption = strtolower((string) env('SMTP_ENCRYPTION', 'tls'));
        $transport = $encryption === 'ssl' ? 'ssl://' : '';
        $socket = @stream_socket_client($transport . $host . ':' . $port, $errorNumber, $errorMessage, 15);
        if (!is_resource($socket)) {
            throw new RuntimeException("SMTP connection failed: {$errorNumber} {$errorMessage}");
        }
        stream_set_timeout($socket, 15);
        $this->expect($socket, [220]);
        $this->command($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), [250]);
        if ($encryption === 'tls') {
            $this->command($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Unable to enable SMTP TLS');
            }
            $this->command($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), [250]);
        }
        $user = (string) env('SMTP_USER', '');
        $password = (string) env('SMTP_PASS', '');
        if ($user !== '') {
            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($user), [334]);
            $this->command($socket, base64_encode($password), [235]);
        }

        $from = (string) env('SMTP_FROM', $user);
        $to = (string) env('SMTP_TO', 'info@almaha-group.com');
        $this->command($socket, 'MAIL FROM:<' . $from . '>', [250]);
        $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
        $this->command($socket, 'DATA', [354]);

        $subject = 'New website inquiry: ' . ($message['subject'] ?? 'Contact');
        $body = implode("\r\n", [
            'Name: ' . ($message['name'] ?? ''),
            'Company: ' . ($message['company'] ?? ''),
            'Email: ' . ($message['email'] ?? ''),
            'Phone: ' . ($message['phone'] ?? ''),
            'Country: ' . ($message['country'] ?? ''),
            'Request type: ' . ($message['request_type'] ?? ''),
            'Service: ' . ($message['service'] ?? ''),
            '',
            (string) ($message['message'] ?? ''),
        ]);
        $headers = [
            'From: ' . (string) env('SMTP_FROM_NAME', 'Al-Maha Website') . ' <' . $from . '>',
            'To: <' . $to . '>',
            'Reply-To: <' . str_replace(["\r", "\n"], '', (string) ($message['email'] ?? $from)) . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];
        fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n.", "\n..", $body) . "\r\n.\r\n");
        $this->expect($socket, [250]);
        $this->command($socket, 'QUIT', [221]);
        fclose($socket);
        return true;
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
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('SMTP error: ' . trim($response));
        }
    }
}
