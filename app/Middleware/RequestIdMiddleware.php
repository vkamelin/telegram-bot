<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Logger;
use App\Services\Uuid;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

class RequestIdMiddleware
{
    private string $header;

    public function __construct(string $header = 'X-Request-Id')
    {
        $this->header = $header;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $requestId = $request->getHeaderLine($this->header);
        if ($requestId === '') {
            $requestId = Uuid::generate();
        }

        $request = $request->withAttribute('request_id', $requestId);
        Logger::setRequestId($requestId);

        $response = $handler->handle($request);

        Logger::setRequestId(null);

        return $response->withHeader($this->header, $requestId);
    }
}
