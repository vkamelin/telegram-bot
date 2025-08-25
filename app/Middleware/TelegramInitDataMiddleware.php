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
use Vlsv\TelegramInitDataValidator\Validator\InitData;

/**
 * Middleware для проверки и извлечения Telegram init data.
 */
final class TelegramInitDataMiddleware implements MiddlewareInterface
{
    /**
     * @param string $botToken Токен бота для проверки подписи
     */
    public function __construct(private string $botToken) {}

    /**
     * Валидирует init data и добавляет данные пользователя в запрос.
     *
     * @param Req $req HTTP-запрос
     * @param Handler $handler Следующий обработчик
     * @return Res Ответ после обработки
     */
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
            $initDataResult = InitData::isValid($init, $_ENV['BOT_TOKEN'], true);
        } catch (\Throwable $e) {
            return Response::problem(new \Slim\Psr7\Response(), 403, 'Invalid init data');
        }

        if ($initDataResult) {
            parse_str($init, $data);
            $user = json_decode($data['user'] ?? '{}', true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($user)) {
                $user = [];
            }
        }

        $telegramUser = [
            'id' => isset($user['id']) ? (int)$user['id'] : null,
        ];
        if (isset($user['username'])) {
            $telegramUser['username'] = $user['username'];
        }
        if (isset($user['language_code'])) {
            $telegramUser['language_code'] = $user['language_code'];
        }

        $req = $req
            ->withAttribute('telegramUser', $telegramUser)
            ->withAttribute('rawInitData', $init);

        return $handler->handle($req);
    }
}
