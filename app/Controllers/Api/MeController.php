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
 * Контроллер для получения информации о текущем пользователе.
 */
final class MeController
{
    /**
     * @param PDO $pdo Подключение к базе данных
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Возвращает данные авторизованного пользователя.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res JSON с данными пользователя
     */
    public function show(Req $req, Res $res): Res
    {
        $jwt = (array)$req->getAttribute('jwt');
        $uid = (int)($jwt['uid'] ?? 0);
        if ($uid <= 0) {
            return Response::problem($res, 401, 'Unauthorized');
        }
        $stmt = $this->pdo->prepare('SELECT id, email, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $u = $stmt->fetch();
        if (!$u) {
            return Response::problem($res, 404, 'User not found');
        }
        return Response::json($res, 200, ['user' => $u]);
    }
}
