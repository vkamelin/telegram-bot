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

        // Collect environment variables, mask sensitive values
        $env = $_ENV;
        ksort($env);
        $masked = [];
        foreach ($env as $k => $v) {
            $val = (string)$v;
            if (preg_match('/(SECRET|PASS|TOKEN|KEY)/i', $k)) {
                $len = strlen($val);
                $masked[$k] = $len > 6 ? substr($val, 0, 3) . str_repeat('*', $len - 6) . substr($val, -3) : str_repeat('*', $len);
            } else {
                $masked[$k] = $val;
            }
        }

        // Load full config array
        /** @var array $configArr */
        $configArr = require __DIR__ . '/../../Config/config.php';

        $workerCommands = [
            'status' => [
                'supervisorctl status gpt:* tg:* lp',
                'tail -n 100 storage/logs/*.log',
            ],
            'restart' => [
                'supervisorctl restart gpt:* tg:* lp',
            ],
        ];

        $queueSizes = null;
        $sendSpeed = null;
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
                    'SELECT COUNT(*) FROM telegram_messages ' .
                    "WHERE status='success' AND processed_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)"
                );
                $sendSpeed = (int)$stmt->fetchColumn();
            } catch (\Throwable) {
                $sendSpeed = null;
            }
        }

        $data = [
            'title' => 'System',
            'health' => $health,
            'env' => $masked,
            'config' => $configArr,
            'workerCommands' => $workerCommands,
            'queueSizes' => $queueSizes,
            'sendSpeed' => $sendSpeed,
        ];

        return View::render($res, 'dashboard/system.php', $data, 'layouts/main.php');
    }
}
