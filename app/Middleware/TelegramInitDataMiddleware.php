<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Response;
use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Vlsv\TelegramDataValidator\TelegramDataValidator;

final class TelegramInitDataMiddleware implements MiddlewareInterface
{
    public function __construct(private string $botToken) {}

    public function process(Req $req, Handler $handler): Res
    {
        $init = '';

        $auth = $req->getHeaderLine('Authorization');
        if (preg_match('~^tma\s+(.+)$~i', $auth, $m)) {
            $init = $m[1];
        }

        if ($init === '') {
            $init = $req->getHeaderLine('X-Telegram-Init-Data');
        }

        if ($init === '') {
            $params = array_merge($req->getQueryParams(), (array)$req->getParsedBody());
            $init = $params['initData'] ?? '';
        }

        if ($init === '') {
            return Response::problem(new \Slim\Psr7\Response(), 403, 'Missing init data');
        }

        try {
            (new TelegramDataValidator($this->botToken))->validate($init);
        } catch (\Throwable $e) {
            return Response::problem(new \Slim\Psr7\Response(), 403, 'Invalid init data');
        }

        parse_str($init, $data);
        $user = json_decode($data['user'] ?? '{}', true);

        $req = $req
            ->withAttribute('tg_user_id', isset($user['id']) ? (int)$user['id'] : null)
            ->withAttribute('tg_username', $user['username'] ?? null)
            ->withAttribute('tg_language_code', $user['language_code'] ?? null)
            ->withAttribute('tg_init_data', $init);

        return $handler->handle($req);
    }
}
