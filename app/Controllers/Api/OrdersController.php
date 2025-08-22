<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Api;

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use App\Helpers\Response;

final class OrdersController
{
    public function create(Req $req, Res $res): Res
    {
        $data = (array)$req->getParsedBody();
        if (empty($data['item_id'])) {
            return Response::problem($res, 400, 'Validation error', ['errors' => ['item_id' => 'required']]);
        }
        return Response::json($res, 201, ['status' => 'created']);
    }
}
