<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use App\Helpers\View;
use GuzzleHttp\Client;
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
        $health = null;
        try {
            $client = new Client(['timeout' => 2.0]);
            $resp   = $client->get('http://localhost/api/health');
            $health = json_decode((string)$resp->getBody(), true);
        } catch (\Throwable) {
            $health = null;
        }

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

        $data = [
            'title'          => 'System',
            'health'         => $health,
            'env'            => $env,
            'workerCommands' => $workerCommands,
        ];

        return View::render($res, 'dashboard/system.php', $data, 'layouts/main.php');
    }
}
