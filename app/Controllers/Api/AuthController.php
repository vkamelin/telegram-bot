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
 * Контроллер авторизации пользователей.
 *
 * Обрабатывает выдачу JWT токена.
 */
final class AuthController
{
    /**
     * @param PDO   $pdo    Подключение к базе данных
     * @param array $jwtCfg Настройки JWT (secret, alg, ttl)
     */
    public function __construct(private PDO $pdo, private array $jwtCfg) {}

    /**
     * Выполняет вход пользователя и возвращает JWT.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res JSON с токеном или сообщением об ошибке
     */
    public function login(Req $req, Res $res): Res
    {
        $data = (array)$req->getParsedBody();
        $email = (string)($data['email'] ?? '');
        $pass  = (string)($data['password'] ?? '');
        
        $stmt = $this->pdo->prepare('SELECT id, password_hash FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if (!$u || !password_verify($pass, $u['password_hash'])) {
            return Response::problem($res, 401, 'Invalid credentials');
        }
        
        $payload = ['uid' => (int)$u['id'], 'exp' => time() + $this->jwtCfg['ttl']];
        $token = \Firebase\JWT\JWT::encode($payload, $this->jwtCfg['secret'], $this->jwtCfg['alg']);
        
        return Response::json($res, 200, ['token' => $token]);
    }
}