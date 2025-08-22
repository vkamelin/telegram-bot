<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

class ContentSecurityPolicyMiddleware
{
    private string $policy;

    public function __construct(
        ?string $script = null,
        ?string $style = null,
        ?string $img = null,
        ?string $connect = null,
    ) {
        $scriptSrc = $this->buildSources($script ?? getenv('CSP_SCRIPT_SRC') ?: '');
        $styleSrc = $this->buildSources($style ?? getenv('CSP_STYLE_SRC') ?: '', true);
        $imgSrc = $this->buildSources($img ?? getenv('CSP_IMG_SRC') ?: '');
        $connectSrc = $this->buildSources($connect ?? getenv('CSP_CONNECT_SRC') ?: '');

        $this->policy = sprintf(
            "default-src 'self'; connect-src %s; img-src %s data:; script-src %s; style-src %s; frame-ancestors 'none'",
            $connectSrc,
            $imgSrc,
            $scriptSrc,
            $styleSrc,
        );
    }

    private function buildSources(string $domains, bool $allowUnsafeInline = false): string
    {
        $sources = "'self'";
        if ($allowUnsafeInline) {
            $sources .= " 'unsafe-inline'";
        }
        $additional = array_filter(array_map('trim', explode(',', $domains)));
        if ($additional !== []) {
            $sources .= ' ' . implode(' ', $additional);
        }

        return $sources;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        return $response->withHeader('Content-Security-Policy', $this->policy);
    }
}
