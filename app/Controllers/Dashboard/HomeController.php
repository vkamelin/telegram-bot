<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;

/**
 * Контроллер главной страницы панели управления.
 */
final class HomeController
{
    /**
     * Отображает статус панели.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res Ответ со статусом
     */
    public function index(Req $req, Res $res): Res
    {
        $res->getBody()->write('Dashboard OK');
        return $res;
    }
}