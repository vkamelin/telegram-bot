<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use App\Helpers\Response;

final class TelegramInitDataMiddleware implements MiddlewareInterface
{
    public function __construct(private string $botToken) {}

    public function process(Req $req, Handler $handler): Res
    {
        $init = $req->getHeaderLine('X-Telegram-Init-Data');
        if ($init === '') {
            return Response::problem(new \Slim\Psr7\Response(), 401, 'Missing init data');
        }
        parse_str($init, $data);
        if (!isset($data['hash'])) {
            return Response::problem(new \Slim\Psr7\Response(), 401, 'Invalid init data');
        }
        return $handler->handle($req->withAttribute('tg_init', $data));
    }
}
