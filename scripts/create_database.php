<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';

$config = require BASE_PATH . '/config/database.php';
$default = $config['default'];
$connection = $config['connections'][$default];

if ($connection['driver'] === 'sqlite') {
    $database = $connection['database'];
    $directory = dirname($database);

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    touch($database);
    echo "Arquivo SQLite pronto em {$database}" . PHP_EOL;
    exit;
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;charset=%s',
    $connection['host'],
    $connection['port'],
    $connection['charset'] ?? 'utf8mb4'
);

$pdo = new PDO($dsn, $connection['username'], $connection['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$database = $connection['database'];
if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $database)) {
    throw new RuntimeException('Nome de banco invalido no .env.');
}

$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

echo "Banco {$database} criado ou ja existente." . PHP_EOL;
