<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Api;

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use App\Helpers\Response;

/**
 * Контроллер для работы с товарами.
 */
final class ItemsController
{
    /**
     * Возвращает список доступных товаров.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res JSON со списком товаров
     */
    public function list(Req $req, Res $res): Res
    {
        return Response::json($res, 200, ['items' => []]);
    }
}
