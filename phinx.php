<?php

declare(strict_types=1);

$config = require __DIR__ . '/app/Config/config.php';
$db = $config['db'];
$dsn = $db['dsn'] ?? '';
$adapter = explode(':', $dsn, 2)[0] ?: 'mysql';

return [
    'paths' => [
        'migrations' => 'database/migrations',
        'seeds' => 'database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'default',
        'default' => [
            'adapter' => $adapter,
            'dsn' => $dsn,
            'user' => $db['user'] ?? null,
            'pass' => $db['pass'] ?? null,
        ],
    ],
];
