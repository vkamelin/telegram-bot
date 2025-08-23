<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Api;

use PDO;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use App\Helpers\Response;

/**
 * Контроллер проверки состояния сервиса.
 *
 * Выполняет простой запрос к базе данных и возвращает статус приложения.
 */
final class HealthController
{
    public function __construct(private PDO $pdo) {}

    /**
     * Проверяет работоспособность базы данных и возвращает статус.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res JSON со статусом сервиса
     */
    public function __invoke(Req $req, Res $res): Res
    {
        $this->pdo->query('SELECT 1');
        return Response::json($res, 200, ['status' => 'ok']);
    }
}
