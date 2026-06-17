<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

final class Database
{
    private static array $config = [];
    private static ?PDO $pdo = null;
    private static string $driver = 'mysql';

    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$pdo = null;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $default = self::$config['default'] ?? 'mysql';
        $connection = self::$config['connections'][$default] ?? null;

        if ($connection === null) {
            throw new RuntimeException("Conexao '{$default}' nao encontrada.");
        }

        self::$driver = $connection['driver'];

        if (self::$driver === 'sqlite') {
            $database = $connection['database'];
            $directory = dirname($database);

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            self::$pdo = new PDO('sqlite:' . $database);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->exec('PRAGMA foreign_keys = ON');

            return self::$pdo;
        }

        $charset = $connection['charset'] ?? 'utf8mb4';
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $connection['host'],
            $connection['port'],
            $connection['database'],
            $charset
        );

        self::$pdo = new PDO($dsn, $connection['username'], $connection['password']);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return self::$pdo;
    }

    public static function driver(): string
    {
        if (!self::$pdo instanceof PDO) {
            self::pdo();
        }

        return self::$driver;
    }

    public static function statement(string $sql, array $bindings = []): bool
    {
        $statement = self::pdo()->prepare($sql);

        return $statement->execute($bindings);
    }

    public static function select(string $sql, array $bindings = []): array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($bindings);

        return $statement->fetchAll();
    }

    public static function quoteIdentifier(string $identifier): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            throw new RuntimeException("Identificador invalido: {$identifier}");
        }

        return self::driver() === 'mysql' ? "`{$identifier}`" : "\"{$identifier}\"";
    }
}
