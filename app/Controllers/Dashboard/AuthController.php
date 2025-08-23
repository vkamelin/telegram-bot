<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use App\Helpers\View;

/**
 * Контроллер авторизации панели управления.
 */
final class AuthController
{
    /**
     * Отображает форму входа.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res Ответ с формой входа
     */
    public function showLogin(Req $req, Res $res): Res
    {
        $data = [
            'title' => 'Login',
            'error' => '',
        ];

        return View::render($res, 'dashboard/login.php', $data, 'layouts/centered.php');
    }

    /**
     * Выполняет вход пользователя.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res Ответ после входа
     */
    public function login(Req $req, Res $res): Res
    {
        global $pdo;

        $data = (array)$req->getParsedBody();
        $email = (string)($data['email'] ?? '');
        $pass  = (string)($data['password'] ?? '');

        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if (!$u || !password_verify($pass, $u['password_hash'])) {
            $params = [
                'title' => 'Login',
                'error' => 'Invalid credentials',
            ];
            return View::render($res, 'dashboard/login.php', $params, 'layouts/centered.php');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id'];

        return $res->withHeader('Location', '/dashboard')->withStatus(302);
    }

    /**
     * Завершает сессию пользователя.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res Ответ после выхода
     */
    public function logout(Req $req, Res $res): Res
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        return $res->withHeader('Location', '/dashboard/login')->withStatus(302);
    }
}
