<?php

declare(strict_types=1);

return [
    'app_env' => getenv('APP_ENV') ?: 'prod',
    'debug' => (bool)(getenv('APP_DEBUG') ?: false),
    
    'db' => [
        'dsn' => getenv('DB_DSN'),   // 'mysql:host=127.0.0.1;dbname=app;charset=utf8mb4'
        'user' => getenv('DB_USER'),
        'pass' => getenv('DB_PASS'),
        'opts' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ],
    ],
    
    'jwt' => [
        'secret' => getenv('JWT_SECRET'),
        'alg' => 'HS256',
        'ttl' => 3600,
    ],
    
    'cors' => [
        'origins' => explode(',', getenv('CORS_ORIGINS') ?: ''),
        'methods' => 'GET,POST,PUT,PATCH,DELETE,OPTIONS',
        'headers' => 'Authorization,Content-Type,X-Request-Id',
    ],
    
    'rate_limit' => [
        'bucket' => 'ip',   // 'ip' или 'user'
        'limit' => 60,     // запросов в минуту
    ],
];
