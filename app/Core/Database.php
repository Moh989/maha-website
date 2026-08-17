<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = require base_path('config/database.php');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $initCommandAttribute = PHP_VERSION_ID >= 80500 && class_exists(\Pdo\Mysql::class)
                ? \Pdo\Mysql::ATTR_INIT_COMMAND
                : PDO::MYSQL_ATTR_INIT_COMMAND;
            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                $initCommandAttribute => "SET NAMES {$config['charset']} COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $exception) {
            error_log('Database connection failed: ' . $exception->getMessage());
            throw new RuntimeException('تعذر الاتصال بقاعدة البيانات. راجع إعدادات البيئة.', 0, $exception);
        }

        return self::$connection;
    }

    public static function reset(): void
    {
        self::$connection = null;
    }
}
