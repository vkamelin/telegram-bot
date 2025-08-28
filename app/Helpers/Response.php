<?php

/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Res;

/**
 * Вспомогательные методы для формирования HTTP-ответов.
 */
final class Response
{
    /**
     * Возвращает JSON-ответ.
     *
     * @param Res $res HTTP-ответ
     * @param int $status HTTP-статус
     * @param array $data Данные для сериализации
     * @return Res JSON-ответ
     */
    public static function json(Res $res, int $status, array $data): Res
    {
        $res->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $res->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    /**
     * Возвращает ответ в формате application/problem+json.
     *
     * @param Res $res HTTP-ответ
     * @param int $status HTTP-статус
     * @param string $title Заголовок ошибки
     * @param array $extra Дополнительные поля
     * @return Res Ответ с описанием проблемы
     */
    public static function problem(Res $res, int $status, string $title, array $extra = []): Res
    {
        $body = array_merge([
            'type' => 'about:blank',
            'title' => $title,
            'status' => $status,
        ], $extra);
        $res->getBody()->write(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $res->withHeader('Content-Type', 'application/problem+json')->withStatus($status);
    }
}
