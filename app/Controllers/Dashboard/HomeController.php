<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;

final class HomeController
{
    public function index(Req $req, Res $res): Res
    {
        $res->getBody()->write('<h1>Dashboard</h1>');
        return $res;
    }
}