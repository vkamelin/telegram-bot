<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Handlers\ApiErrorHandler;
use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpException;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;

final class ErrorMiddleware implements MiddlewareInterface
{
    private ApiErrorHandler $apiHandler;
    private ResponseFactory $responseFactory;

    public function __construct(private bool $debug, ?ApiErrorHandler $apiHandler = null, ?ResponseFactory $responseFactory = null)
    {
        $this->apiHandler = $apiHandler ?? new ApiErrorHandler();
        $this->responseFactory = $responseFactory ?? new ResponseFactory();
    }

    public function process(Req $req, Handler $handler): Res
    {
        try {
            return $handler->handle($req);
        } catch (Throwable $e) {
            $path = $req->getUri()->getPath();
            $accept = $req->getHeaderLine('Accept');
            $wantsJson = str_starts_with($path, '/api') || str_contains($accept, 'application/json');

            if ($wantsJson) {
                return $this->apiHandler->handle($e);
            }

            $status = $e instanceof HttpException ? $e->getCode() : 500;
            $message = $this->debug ? $e->getMessage() : 'Internal Server Error';
            $response = $this->responseFactory->createResponse($status);
            $response->getBody()->write($message);
            return $response->withHeader('Content-Type', 'text/plain');
        }
    }
}
