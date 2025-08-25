<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use App\Helpers\Database;
use App\Helpers\RedisHelper;
use App\Helpers\View;
use App\Services\HealthService;
use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Message\ServerRequestInterface as Req;

/**
 * Контроллер системной информации.
 */
final class SystemController
{
    /**
     * Отображает системные параметры и команды для воркеров.
     */
    public function index(Req $req, Res $res): Res
    {
        $health = HealthService::check();

        $envKeys = [
            'APP_ENV',
            'APP_DEBUG',
            'DB_DSN',
            'DB_USER',
            'REDIS_DSN',
            'TELEMETRY_ENABLED',
        ];
        $env = [];
        foreach ($envKeys as $key) {
            $env[$key] = $_ENV[$key] ?? null;
        }

        $workerCommands = [
            'status' => [
                'ps aux | grep workers/telegram.php',
                'tail -n 100 storage/logs/*.log',
            ],
            'restart' => [
                'pkill -f workers/telegram.php',
                'php workers/telegram.php &',
            ],
        ];

        $queueSizes = null;
        $sendSpeed  = null;
        if (filter_var($_ENV['TELEMETRY_ENABLED'] ?? false, FILTER_VALIDATE_BOOL)) {
            try {
                $redis = RedisHelper::getInstance();
                $queueSizes = [
                    'p2' => (int)$redis->lLen('telegram:queue:2'),
                    'p1' => (int)$redis->lLen('telegram:queue:1'),
                    'p0' => (int)$redis->lLen('telegram:queue:0'),
                    'dlq' => (int)$redis->lLen('telegram:dlq'),
                ];
            } catch (\RedisException) {
                $queueSizes = null;
            }

            try {
                $db = Database::getInstance();
                $stmt = $db->query(
                    "SELECT COUNT(*) FROM telegram_messages " .
                    "WHERE status='success' AND processed_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
                );
                $sendSpeed = (int)$stmt->fetchColumn();
            } catch (\Throwable) {
                $sendSpeed = null;
            }
        }

        $data = [
            'title'          => 'System',
            'health'         => $health,
            'env'            => $env,
            'workerCommands' => $workerCommands,
            'queueSizes'     => $queueSizes,
            'sendSpeed'      => $sendSpeed,
        ];

        return View::render($res, 'dashboard/system.php', $data, 'layouts/main.php');
    }
}
