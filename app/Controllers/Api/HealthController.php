<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Api;

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use App\Helpers\Response;
use App\Services\HealthService;

/**
 * Контроллер проверки состояния сервиса.
 *
 * Выполняет простой запрос к базе данных и возвращает статус приложения.
 */
final class HealthController
{

    /**
     * Проверяет работоспособность сервисов и возвращает статус.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res JSON со статусом сервиса
     */
    public function __invoke(Req $req, Res $res): Res
    {
        $details = HealthService::check();
        $overall = $details['status'] === 'ok';

        return Response::json($res, $overall ? 200 : 503, $details);
    }
}
