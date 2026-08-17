<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Logger
{
    private const FATAL_ERRORS = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR];
    private const SECRET_KEY_PATTERN = '/pass(word)?|token|secret|authorization|cookie|session|csrf|api[_-]?key/i';

    private static string $path = '';
    private static string $requestId = '';
    private static bool $initialized = false;
    private static bool $debug = false;
    private static bool $terminalLogged = false;
    private static float $startedAt = 0.0;

    public static function boot(string $path, bool $debug = false): void
    {
        if (self::$initialized) {
            return;
        }

        self::$path = $path;
        self::$debug = $debug;
        self::$startedAt = defined('APP_START') ? (float) APP_START : microtime(true);
        self::$requestId = self::makeRequestId();
        self::$initialized = true;

        self::prepareLogFile();
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', dirname(self::$path) . '/php-native.log');
        error_reporting(E_ALL);

        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            header('X-Request-ID: ' . self::$requestId);
        }

        set_error_handler([self::class, 'handlePhpError']);
        set_exception_handler([self::class, 'handleUncaughtException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function requestId(): string
    {
        return self::$requestId;
    }

    public static function warning(string $event, string $message, array $context = []): void
    {
        self::write('warning', $event, $message, $context);
    }

    public static function error(string $event, string $message, array $context = []): void
    {
        self::write('error', $event, $message, $context);
    }

    public static function critical(string $event, string $message, array $context = []): void
    {
        self::write('critical', $event, $message, $context);
    }

    public static function exception(string $event, Throwable $exception, array $context = [], string $level = 'error'): void
    {
        self::write($level, $event, $exception->getMessage(), $context, [
            'class' => $exception::class,
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'previous' => self::previousExceptions($exception),
        ]);
    }

    public static function handlePhpError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $level = match ($severity) {
            E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, 2048 => 'notice',
            E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING => 'warning',
            default => 'error',
        };

        self::write($level, 'PHP_ERROR', $message, [
            'severity' => $severity,
            'severity_name' => self::severityName($severity),
            'trace' => self::warningTrace(),
        ], null, $file, $line);

        return !in_array($severity, [E_USER_ERROR, E_RECOVERABLE_ERROR], true);
    }

    public static function handleUncaughtException(Throwable $exception): void
    {
        self::$terminalLogged = true;
        self::exception('UNCAUGHT_EXCEPTION', $exception, [], 'critical');

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, "Unexpected error. Request ID: " . self::$requestId . PHP_EOL);
            return;
        }

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            header('X-Request-ID: ' . self::$requestId);
        }

        $isArabic = ($_SESSION['locale'] ?? 'ar') === 'ar';
        $title = $isArabic ? 'حدث خطأ غير متوقع' : 'An unexpected error occurred';
        $message = $isArabic
            ? 'تم تسجيل الخطأ. يرجى تزويد الدعم برقم التتبع الظاهر أدناه.'
            : 'The error has been logged. Please provide support with the tracking ID below.';
        $requestId = htmlspecialchars(self::$requestId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $details = '';
        if (self::$debug) {
            $debugText = $exception::class . ': ' . $exception->getMessage() . "\n" . $exception->getTraceAsString();
            $details = '<pre style="white-space:pre-wrap;text-align:left;direction:ltr">' . htmlspecialchars($debugText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
        }

        echo '<!doctype html><html lang="' . ($isArabic ? 'ar' : 'en') . '" dir="' . ($isArabic ? 'rtl' : 'ltr') . '"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'
            . $title . '</title><body style="margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:#07192c;color:#fff;font-family:Tajawal,Arial,sans-serif"><main style="max-width:760px"><h1>'
            . $title . '</h1><p>' . $message . '</p><p dir="ltr"><strong>Request ID:</strong> ' . $requestId . '</p>' . $details . '</main></body></html>';
    }

    public static function handleShutdown(): void
    {
        $lastError = error_get_last();
        if ($lastError && in_array((int) $lastError['type'], self::FATAL_ERRORS, true) && !self::$terminalLogged) {
            self::$terminalLogged = true;
            self::write('critical', 'PHP_FATAL', (string) $lastError['message'], [
                'severity' => (int) $lastError['type'],
                'severity_name' => self::severityName((int) $lastError['type']),
            ], null, (string) $lastError['file'], (int) $lastError['line']);
        }

        if (PHP_SAPI === 'cli' || self::$terminalLogged) {
            return;
        }

        $status = http_response_code();
        if (is_int($status) && $status >= 400) {
            self::write($status >= 500 ? 'error' : 'warning', 'HTTP_STATUS', "Request completed with HTTP {$status}", [
                'status' => $status,
            ]);
        }
    }

    private static function write(
        string $level,
        string $event,
        string $message,
        array $context = [],
        ?array $exception = null,
        ?string $file = null,
        ?int $line = null
    ): void {
        if (!self::$initialized || self::$path === '') {
            error_log("[{$level}] {$event}: {$message}");
            return;
        }

        try {
            self::rotateIfNeeded();
            $entry = [
                'timestamp' => date(DATE_ATOM),
                'level' => $level,
                'event' => $event,
                'message' => self::cleanString($message, 8000),
                'request_id' => self::$requestId,
                'request' => self::requestContext(),
                'source' => $file !== null ? ['file' => $file, 'line' => $line] : null,
                'exception' => $exception,
                'context' => self::redact($context),
                'runtime' => [
                    'php' => PHP_VERSION,
                    'sapi' => PHP_SAPI,
                    'duration_ms' => round((microtime(true) - self::$startedAt) * 1000, 2),
                    'memory_peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
                ],
            ];

            $json = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            if (is_string($json)) {
                @file_put_contents(self::$path, $json . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        } catch (Throwable) {
            // Logging must never break the website or mask the original error.
        }
    }

    private static function requestContext(): array
    {
        $uri = self::sanitizeUri((string) ($_SERVER['REQUEST_URI'] ?? ''));
        $referer = self::sanitizeUri((string) ($_SERVER['HTTP_REFERER'] ?? ''));

        return [
            'method' => strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? (PHP_SAPI === 'cli' ? 'CLI' : 'UNKNOWN'))),
            'host' => self::cleanString((string) ($_SERVER['HTTP_HOST'] ?? ''), 255),
            'path' => $uri['path'],
            'query' => $uri['query'],
            'ip' => self::cleanString((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 45),
            'user_agent' => self::cleanString((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 500),
            'referer' => $referer,
            'user_id' => isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
        ];
    }

    private static function sanitizeUri(string $uri): array
    {
        if ($uri === '') {
            return ['path' => '', 'query' => []];
        }

        $parts = parse_url($uri);
        if (!is_array($parts)) {
            return ['path' => self::cleanString($uri, 2000), 'query' => []];
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str((string) $parts['query'], $query);
        }

        return [
            'path' => self::cleanString((string) ($parts['path'] ?? ''), 2000),
            'query' => self::redact($query),
        ];
    }

    private static function redact(mixed $value, int $depth = 0, ?string $key = null): mixed
    {
        if ($key !== null && preg_match(self::SECRET_KEY_PATTERN, $key)) {
            return '[REDACTED]';
        }
        if ($depth >= 5) {
            return '[DEPTH_LIMIT]';
        }
        if (is_array($value)) {
            $safe = [];
            foreach ($value as $itemKey => $itemValue) {
                $safe[$itemKey] = self::redact($itemValue, $depth + 1, is_string($itemKey) ? $itemKey : null);
            }
            return $safe;
        }
        if (is_string($value)) {
            return self::cleanString($value, 4000);
        }
        if (is_object($value)) {
            return '[OBJECT ' . $value::class . ']';
        }
        if (is_resource($value)) {
            return '[RESOURCE]';
        }
        return $value;
    }

    private static function warningTrace(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 14);
        array_shift($trace);
        return array_map(static fn (array $frame): array => [
            'file' => $frame['file'] ?? null,
            'line' => $frame['line'] ?? null,
            'call' => ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? ''),
        ], $trace);
    }

    private static function previousExceptions(Throwable $exception): array
    {
        $previous = [];
        $current = $exception->getPrevious();
        while ($current !== null && count($previous) < 5) {
            $previous[] = [
                'class' => $current::class,
                'code' => $current->getCode(),
                'message' => self::cleanString($current->getMessage(), 4000),
                'file' => $current->getFile(),
                'line' => $current->getLine(),
            ];
            $current = $current->getPrevious();
        }
        return $previous;
    }

    private static function severityName(int $severity): string
    {
        return match ($severity) {
            E_ERROR => 'E_ERROR', E_WARNING => 'E_WARNING', E_PARSE => 'E_PARSE', E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR', E_CORE_WARNING => 'E_CORE_WARNING', E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING', E_USER_ERROR => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE', 2048 => 'E_STRICT', E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED', E_USER_DEPRECATED => 'E_USER_DEPRECATED', default => 'UNKNOWN',
        };
    }

    private static function cleanString(string $value, int $limit): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
        return mb_strlen($value) > $limit ? mb_substr($value, 0, $limit) . '…' : $value;
    }

    private static function makeRequestId(): string
    {
        try {
            return date('YmdHis') . '-' . bin2hex(random_bytes(8));
        } catch (Throwable) {
            return date('YmdHis') . '-' . str_replace('.', '', uniqid('', true));
        }
    }

    private static function prepareLogFile(): void
    {
        $directory = dirname(self::$path);
        if (!is_dir($directory)) {
            @mkdir($directory, 0750, true);
        }
        if (!is_file(self::$path)) {
            @touch(self::$path);
        }
        @chmod(self::$path, 0640);
        $nativePath = $directory . '/php-native.log';
        if (!is_file($nativePath)) {
            @touch($nativePath);
        }
        @chmod($nativePath, 0640);
    }

    private static function rotateIfNeeded(): void
    {
        clearstatcache(true, self::$path);
        $maxBytes = max(1, (int) env('LOG_MAX_MB', 10)) * 1048576;
        if (!is_file(self::$path) || (int) @filesize(self::$path) < $maxBytes) {
            return;
        }

        $copies = min(20, max(1, (int) env('LOG_MAX_FILES', 5)));
        if (is_file(self::$path . '.' . $copies)) {
            @unlink(self::$path . '.' . $copies);
        }
        for ($index = $copies - 1; $index >= 1; $index--) {
            if (is_file(self::$path . '.' . $index)) {
                @rename(self::$path . '.' . $index, self::$path . '.' . ($index + 1));
            }
        }
        @rename(self::$path, self::$path . '.1');
        @touch(self::$path);
        @chmod(self::$path, 0640);
    }
}
