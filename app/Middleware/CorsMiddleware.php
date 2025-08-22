<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;

class CorsMiddleware
{
    /**
     * @var string[]
     */
    private array $allowedOrigins;
    private ResponseFactory $responseFactory;

    public function __construct(?string $origins = null, ?ResponseFactory $responseFactory = null)
    {
        $origins = $origins ?? ($_ENV['CORS_ORIGIN'] ?? '');
        $this->allowedOrigins = array_filter(array_map('trim', explode(',', $origins)));
        $this->responseFactory = $responseFactory ?? new ResponseFactory();
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            $response = $this->responseFactory->createResponse(204);
        } else {
            $response = $handler->handle($request);
        }

        $origin = $request->getHeaderLine('Origin');
        if ($origin !== '' && in_array($origin, $this->allowedOrigins, true)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response
            ->withHeader('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
