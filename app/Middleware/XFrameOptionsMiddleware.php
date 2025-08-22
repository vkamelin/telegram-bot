<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

class XFrameOptionsMiddleware
{
    private string $option;

    public function __construct(string $option = 'DENY')
    {
        $this->option = $option;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        return $response->withHeader('X-Frame-Options', $this->option);
    }
}
