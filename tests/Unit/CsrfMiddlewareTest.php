<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response as Psr7Response;
use App\Middleware\CsrfMiddleware;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;

final class CsrfMiddlewareTest extends TestCase
{
    public function testAllowsRequestWhenTokensMatch(): void
    {
        $middleware = new CsrfMiddleware();

        $req = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withParsedBody(['_csrf_token' => 'token'])
            ->withCookieParams(['_csrf_token' => 'token']);
        $handler = new class implements RequestHandlerInterface {
            public function handle(Req $request): Res
            {
                return new Psr7Response();
            }
        };

        $res = $middleware->process($req, $handler);
        $this->assertSame(200, $res->getStatusCode());
    }

    public function testRejectsRequestWhenTokensMismatch(): void
    {
        $middleware = new CsrfMiddleware();

        $req = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withParsedBody(['_csrf_token' => 'one'])
            ->withCookieParams(['_csrf_token' => 'two']);
        $handler = new class implements RequestHandlerInterface {
            public function handle(Req $request): Res
            {
                return new Psr7Response();
            }
        };

        $res = $middleware->process($req, $handler);
        $this->assertSame(403, $res->getStatusCode());
    }

    public function testRejectsRequestWhenCookieMissing(): void
    {
        $middleware = new CsrfMiddleware();

        $req = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withParsedBody(['_csrf_token' => 'token']);
        $handler = new class implements RequestHandlerInterface {
            public function handle(Req $request): Res
            {
                return new Psr7Response();
            }
        };

        $res = $middleware->process($req, $handler);
        $this->assertSame(403, $res->getStatusCode());
    }

    public function testRejectsRequestWhenFormTokenMissing(): void
    {
        $middleware = new CsrfMiddleware();

        $req = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withCookieParams(['_csrf_token' => 'token']);
        $handler = new class implements RequestHandlerInterface {
            public function handle(Req $request): Res
            {
                return new Psr7Response();
            }
        };

        $res = $middleware->process($req, $handler);
        $this->assertSame(403, $res->getStatusCode());
    }
}
