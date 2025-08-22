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
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

final class JwtMiddleware implements MiddlewareInterface
{
    public function __construct(private array $cfg) {}
    
    public function process(Req $req, Handler $handler): Res
    {
        $auth = $req->getHeaderLine('Authorization');
        if (!preg_match('~^Bearer\s+(.+)$~i', $auth, $m)) {
            return Response::problem(new \Slim\Psr7\Response(), 401, 'Unauthorized');
        }
        try {
            $decoded = JWT::decode($m[1], new Key($this->cfg['secret'], $this->cfg['alg']));
            return $handler->handle($req->withAttribute('jwt', (array)$decoded));
        } catch (Throwable $e) {
            return Response::problem(new \Slim\Psr7\Response(), 401, 'Invalid token');
        }
    }
}