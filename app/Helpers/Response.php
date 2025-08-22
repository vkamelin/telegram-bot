<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Res;

final class Response
{
    public static function json(Res $res, int $status, array $data): Res
    {
        $res->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $res->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
    
    public static function problem(Res $res, int $status, string $title, array $extra = []): Res
    {
        $body = array_merge([
            'type'   => 'about:blank',
            'title'  => $title,
            'status' => $status,
        ], $extra);
        $res->getBody()->write(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $res->withHeader('Content-Type', 'application/problem+json')->withStatus($status);
    }
}