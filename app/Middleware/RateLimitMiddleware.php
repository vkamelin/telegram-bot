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

/**
 * Middleware для ограничения количества запросов.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @param array $cfg Параметры лимитирования
     */
    public function __construct(private array $cfg)
    {
    }

    /**
     * Применяет лимит запросов на основе IP или Telegram пользователя.
     *
     * @param Req $req HTTP-запрос
     * @param Handler $handler Следующий обработчик
     * @return Res Ответ после проверки
     */
    public function process(Req $req, Handler $handler): Res
    {
        // Простой лимит по IP в памяти процесса (для прод — подключите Redis)
        static $hits = [];
        // Используем ID Telegram пользователя, при его отсутствии — IP
        $key = $this->cfg['bucket'] === 'user'
            ? ($req->getAttribute('telegramUser')['id'] ?? $req->getServerParams()['REMOTE_ADDR'] ?? 'anon')
            : ($req->getServerParams()['REMOTE_ADDR'] ?? 'anon');

        $window = (int)(time() / 60);
        $bucket = $key . ':' . $window;

        $hits[$bucket] = ($hits[$bucket] ?? 0) + 1;
        if ($hits[$bucket] > $this->cfg['limit']) {
            return Response::problem(new \Slim\Psr7\Response(), 429, 'Too Many Requests', ['retry_after' => 60]);
        }
        return $handler->handle($req);
    }
}
