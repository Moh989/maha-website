<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use Generator;

final class ErrorLogReader
{
    private string $directory;
    private int $entryLimit;

    public function __construct(?string $directory = null, ?int $entryLimit = null)
    {
        $this->directory = $directory ?? base_path('storage/logs');
        $this->entryLimit = min(5000, max(100, $entryLimit ?? (int) env('LOG_VIEWER_MAX_ENTRIES', 2000)));
    }

    public function search(string $query, string $level, string $source, int $page, int $perPage = 30): array
    {
        $query = mb_substr(trim($query), 0, 200);
        $level = in_array($level, ['critical', 'error', 'warning', 'notice'], true) ? $level : '';
        $source = in_array($source, ['application', 'php-native'], true) ? $source : '';
        $page = max(1, $page);
        $perPage = min(100, max(10, $perPage));

        $entries = $this->recentEntries();
        $stats = ['critical' => 0, 'error' => 0, 'warning' => 0, 'notice' => 0];
        foreach ($entries as $entry) {
            $entryLevel = (string) ($entry['level'] ?? 'error');
            if (isset($stats[$entryLevel])) {
                $stats[$entryLevel]++;
            }
        }

        $filtered = array_values(array_filter($entries, static function (array $entry) use ($query, $level, $source): bool {
            if ($level !== '' && ($entry['level'] ?? '') !== $level) {
                return false;
            }
            if ($source !== '' && ($entry['log_source'] ?? '') !== $source) {
                return false;
            }
            if ($query === '') {
                return true;
            }
            $haystack = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            return is_string($haystack) && mb_stripos($haystack, $query) !== false;
        }));

        $total = count($filtered);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);

        return [
            'entries' => array_slice($filtered, ($page - 1) * $perPage, $perPage),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'stats' => $stats,
            'scanned' => count($entries),
            'limit' => $this->entryLimit,
            'files' => $this->fileMetadata(),
        ];
    }

    private function recentEntries(): array
    {
        $entries = [];
        $remaining = $this->entryLimit;
        $maxFiles = min(20, max(1, (int) env('LOG_MAX_FILES', 5)));

        for ($index = 0; $index <= $maxFiles && $remaining > 0; $index++) {
            $name = 'error_log' . ($index === 0 ? '' : '.' . $index);
            $path = $this->directory . '/' . $name;
            if (!is_file($path) || !is_readable($path)) {
                continue;
            }
            foreach ($this->reverseLines($path, $remaining) as $lineNumber => $line) {
                $decoded = json_decode($line, true);
                if (!is_array($decoded)) {
                    $decoded = $this->nativeEntry($line, $name, (int) $lineNumber, 'application');
                } else {
                    $decoded = $this->normalizeStructuredEntry($decoded, $name, (int) $lineNumber);
                }
                $entries[] = $decoded;
                $remaining--;
                if ($remaining <= 0) {
                    break 2;
                }
            }
        }

        $nativePath = $this->directory . '/php-native.log';
        $nativeLimit = min(300, $this->entryLimit);
        if (is_file($nativePath) && is_readable($nativePath)) {
            foreach ($this->reverseLines($nativePath, $nativeLimit) as $lineNumber => $line) {
                $entries[] = $this->nativeEntry($line, 'php-native.log', (int) $lineNumber, 'php-native');
            }
        }

        usort($entries, static function (array $left, array $right): int {
            return strcmp((string) ($right['timestamp'] ?? ''), (string) ($left['timestamp'] ?? ''));
        });

        return array_slice($entries, 0, $this->entryLimit);
    }

    private function normalizeStructuredEntry(array $entry, string $file, int $lineNumber): array
    {
        $level = strtolower((string) ($entry['level'] ?? 'error'));
        if (!in_array($level, ['critical', 'error', 'warning', 'notice'], true)) {
            $level = 'error';
        }

        return array_merge($entry, [
            'timestamp' => $this->normalizeTimestamp((string) ($entry['timestamp'] ?? '')),
            'level' => $level,
            'event' => (string) ($entry['event'] ?? 'APPLICATION_LOG'),
            'message' => (string) ($entry['message'] ?? ''),
            'request_id' => (string) ($entry['request_id'] ?? ''),
            'log_source' => 'application',
            'log_file' => $file,
            'log_line' => $lineNumber,
        ]);
    }

    private function nativeEntry(string $line, string $file, int $lineNumber, string $source): array
    {
        $timestamp = '';
        $message = trim($line);
        if (preg_match('/^\[([^]]+)]\s*(.*)$/s', $message, $matches)) {
            $timestamp = $this->normalizeTimestamp($matches[1]);
            $message = trim($matches[2]);
        }

        $level = match (true) {
            stripos($message, 'fatal') !== false, stripos($message, 'parse error') !== false => 'critical',
            stripos($message, 'warning') !== false => 'warning',
            stripos($message, 'notice') !== false, stripos($message, 'deprecated') !== false => 'notice',
            default => 'error',
        };

        return [
            'timestamp' => $timestamp,
            'level' => $level,
            'event' => $source === 'php-native' ? 'PHP_NATIVE' : 'UNPARSED_LOG',
            'message' => $this->redactNativeMessage(mb_substr($message, 0, 12000)),
            'request_id' => '',
            'request' => [],
            'source' => null,
            'exception' => null,
            'context' => [],
            'runtime' => [],
            'log_source' => $source,
            'log_file' => $file,
            'log_line' => $lineNumber,
        ];
    }

    private function normalizeTimestamp(string $timestamp): string
    {
        if ($timestamp === '') {
            return '';
        }
        try {
            return (new DateTimeImmutable($timestamp))->format(DATE_ATOM);
        } catch (\Throwable) {
            return mb_substr($timestamp, 0, 80);
        }
    }

    private function redactNativeMessage(string $message): string
    {
        $patterns = [
            '/\b(Bearer)\s+[A-Za-z0-9._~+\/=:-]+/i' => '$1 [REDACTED]',
            '/((?:pass(?:word)?|token|secret|authorization|cookie|session|csrf|api[_-]?key)\s*(?:=|:|=>)\s*)(?:"[^"]*"|\'[^\']*\'|[^\s,;&]+)/i' => '$1[REDACTED]',
            '/([?&](?:pass(?:word)?|token|secret|authorization|cookie|session|csrf|api[_-]?key)=)[^&\s]*/i' => '$1[REDACTED]',
        ];
        return (string) preg_replace(array_keys($patterns), array_values($patterns), $message);
    }

    private function reverseLines(string $path, int $limit): Generator
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return;
        }

        try {
            fseek($handle, 0, SEEK_END);
            $position = ftell($handle);
            if (!is_int($position) || $position <= 0) {
                return;
            }
            $estimatedLine = max(1, $this->countNewlines($handle, $position));
            $remainder = '';
            $yielded = 0;

            while ($position > 0 && $yielded < $limit) {
                $size = min(8192, $position);
                $position -= $size;
                fseek($handle, $position);
                $chunk = fread($handle, $size);
                if (!is_string($chunk)) {
                    break;
                }
                $parts = explode("\n", $chunk . $remainder);
                $remainder = array_shift($parts) ?? '';
                for ($index = count($parts) - 1; $index >= 0 && $yielded < $limit; $index--) {
                    $line = trim($parts[$index]);
                    if ($line === '') {
                        continue;
                    }
                    yield $estimatedLine-- => $line;
                    $yielded++;
                }
            }

            if ($yielded < $limit && trim($remainder) !== '') {
                yield max(1, $estimatedLine) => trim($remainder);
            }
        } finally {
            fclose($handle);
        }
    }

    private function countNewlines($handle, int $size): int
    {
        $sampleSize = min(262144, $size);
        fseek($handle, -$sampleSize, SEEK_END);
        $sample = fread($handle, $sampleSize);
        if (!is_string($sample) || $sample === '') {
            return 1;
        }
        $sampleLines = max(1, substr_count($sample, "\n"));
        return max(1, (int) round(($sampleLines / $sampleSize) * $size));
    }

    private function fileMetadata(): array
    {
        $files = [];
        foreach (['error_log', 'php-native.log'] as $name) {
            $path = $this->directory . '/' . $name;
            clearstatcache(true, $path);
            $files[$name] = [
                'exists' => is_file($path),
                'readable' => is_readable($path),
                'size' => is_file($path) ? (int) (@filesize($path) ?: 0) : 0,
                'modified_at' => is_file($path) && @filemtime($path) ? date(DATE_ATOM, (int) filemtime($path)) : '',
            ];
        }
        return $files;
    }
}
