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
    private string $allowedMethods;
    private string $allowedHeaders;
    private ResponseFactory $responseFactory;

    public function __construct(
        array $origins = [],
        ?string $methods = null,
        ?string $headers = null,
        ?ResponseFactory $responseFactory = null,
    ) {
        $this->allowedOrigins = array_filter(array_map('trim', $origins));
        $this->allowedMethods = $methods ?? 'GET,POST,PUT,PATCH,DELETE,OPTIONS';
        $this->allowedHeaders = $headers ?? 'Content-Type, Authorization';
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
        if ($origin !== '' && (empty($this->allowedOrigins) || in_array($origin, $this->allowedOrigins, true))) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response
            ->withHeader('Access-Control-Allow-Methods', $this->allowedMethods)
            ->withHeader('Access-Control-Allow-Headers', $this->allowedHeaders);
    }
}
