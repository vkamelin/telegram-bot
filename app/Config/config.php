<?php

declare(strict_types=1);

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();

return [
    'app_env' => $_ENV['APP_ENV'] ?? 'prod',
    'debug' => (bool)($_ENV['APP_DEBUG'] ?? false),
    'bot_token' => $_ENV['BOT_TOKEN'] ?? null,
    // Default chat ID for operations where a chat must be specified explicitly
    'default_chat_id' => (int)($_ENV['DEFAULT_CHAT_ID'] ?? 0),

    'db' => [
        'dsn' => $_ENV['DB_DSN'] ?? null,   // 'mysql:host=127.0.0.1;dbname=app;charset=utf8mb4'
        'user' => $_ENV['DB_USER'] ?? null,
        'pass' => $_ENV['DB_PASS'] ?? null,
        'opts' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ],
    ],

    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? null,
        'alg' => 'HS256',
        'ttl' => 3600,
        // time to live for refresh tokens (30 days by default)
        'refresh_ttl' => 60 * 60 * 24 * 30,
    ],

    // API routes list for documentation/tests
    'routes' => [
        'POST /api/auth/login',
        'POST /api/auth/refresh',
    ],

    'cors' => [
        'origins' => explode(',', $_ENV['CORS_ORIGINS'] ?? ''),
        'methods' => 'GET,POST,PUT,PATCH,DELETE,OPTIONS',
        'headers' => 'Authorization,Content-Type,X-Request-Id',
    ],

    'rate_limit' => [
        'bucket' => 'ip',   // 'ip' или 'user'
        'limit' => 60,     // запросов в минуту
    ],

    'request_size_limit' => (int)($_ENV['REQUEST_SIZE_LIMIT'] ?? 1048576),

    // Время жизни ключа идемпотентности по умолчанию
    'IDEMPOTENCY_KEY_TTL' => (int)($_ENV['IDEMPOTENCY_KEY_TTL'] ?? 60),
];
