<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Api;

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use App\Helpers\Response;

final class ItemsController
{
    public function list(Req $req, Res $res): Res
    {
        return Response::json($res, 200, ['items' => []]);
    }
}
