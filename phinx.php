<?php

declare(strict_types=1);

use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__)->safeLoad();

$dsn = $_ENV['DB_DSN'] ?? '';
$adapter = explode(':', $dsn, 2)[0] ?: 'mysql';

$db = [
    'adapter' => $adapter,
    'dsn' => $dsn,
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

