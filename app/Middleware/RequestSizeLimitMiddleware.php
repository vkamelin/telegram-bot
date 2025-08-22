<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Middleware для ограничения размера тела запроса.
 */
class RequestSizeLimitMiddleware implements MiddlewareInterface
{
    private int $maxBytes;

    /**
     * @param int $maxBytes Максимальный размер тела запроса в байтах
     */
    public function __construct(int $maxBytes)
    {
        $this->maxBytes = $maxBytes;
    }

    /**
     * Проверяет размер запроса и отклоняет слишком большие тела.
     *
     * @param Request $request HTTP-запрос
     * @param RequestHandlerInterface $handler Следующий обработчик
     * @return ResponseInterface Ответ после проверки
     */
    public function process(Request $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $length = $request->getServerParams()['CONTENT_LENGTH'] ?? $request->getHeaderLine('Content-Length');
        if ($length !== null && $length !== '' && (int)$length > $this->maxBytes) {
            $response = new Response(413);
            $payload = json_encode(['error' => 'Payload Too Large'], JSON_UNESCAPED_UNICODE);
            if (!is_string($payload)) {
                $payload = '{"error":"Payload Too Large"}';
            }
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

        $bodySize = $request->getBody()->getSize();
        if ($bodySize !== null && $bodySize > $this->maxBytes) {
            $response = new Response(413);
            $payload = json_encode(['error' => 'Payload Too Large'], JSON_UNESCAPED_UNICODE);
            if (!is_string($payload)) {
                $payload = '{"error":"Payload Too Large"}';
            }
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
