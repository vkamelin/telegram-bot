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
use Throwable;

final class ErrorMiddleware implements MiddlewareInterface
{
    public function __construct(private bool $debug) {}
    
    public function process(Req $req, Handler $handler): Res
    {
        try {
            return $handler->handle($req);
        } catch (Throwable $e) {
            $extra = $this->debug ? ['trace' => $e->getTraceAsString()] : [];
            return Response::problem(new \Slim\Psr7\Response(), 500, 'Internal Server Error', $extra);
        }
    }
}