<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use App\Helpers\Response;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function process(Req $req, Handler $handler): Res
    {
        if (in_array($req->getMethod(), ['POST','PUT','PATCH','DELETE'], true)) {
            $body = (array)$req->getParsedBody();
            $h    = $req->getHeaderLine('X-CSRF-Token');
            $t    = (string)($body['_csrf'] ?? '');
            if ($h === '' || $t === '' || !hash_equals($h, $t)) {
                return Response::problem(new \Slim\Psr7\Response(), 403, 'CSRF check failed');
            }
        }
        return $handler->handle($req);
    }
}