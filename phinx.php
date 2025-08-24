<?php

declare(strict_types=1);

use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__)->safeLoad();

$dsn = $_ENV['DB_DSN'] ?? '';

if ($dsn === '') {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $dbname = $_ENV['DB_NAME'] ?? '';
    $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
}

$adapter = explode(':', $dsn, 2)[0] ?: 'mysql';

$db = [
    'adapter' => $adapter,
    'dsn' => $dsn,
    'name' => $_ENV['DB_NAME'] ?? null,
    'user' => $_ENV['DB_USER'] ?? null,
    'pass' => $_ENV['DB_PASS'] ?? null,
];

return [
    'paths' => [
        'migrations' => 'database/migrations',
        'seeds' => 'database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => $_ENV['APP_ENV'] ?? 'development',
        'development' => $db,
        'production' => $db,
    ],
];

