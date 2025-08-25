<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use App\Middleware\TelegramInitDataMiddleware;
use App\Middleware\JwtMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\ErrorMiddleware;
use App\Controllers\Api\MeController;
use App\Controllers\Api\HealthController;
use Firebase\JWT\JWT;
use Slim\Psr7\Response as PsrResponse;

final class ApiTest extends TestCase
{
    private string $botToken = 'TEST_TOKEN';
    private string $jwtSecret = 'jwt-secret';

    private function buildInitData(): string
    {
        $data = [
            'auth_date' => '123',
            'user' => json_encode(['id' => 1], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
        ksort($data);
        $check = [];
        foreach ($data as $k => $v) {
            $check[] = $k . '=' . $v;
        }
        $checkString = implode("\n", $check);
        $secret = hash_hmac('sha256', $this->botToken, 'WebAppData', true);
        $hash = hash_hmac('sha256', $checkString, $secret);
        $data['hash'] = $hash;
        return http_build_query($data);
    }

    private function createApp(): \Slim\App
    {
        $app = AppFactory::create();
        $app->addBodyParsingMiddleware();
        $app->add(new ErrorMiddleware(true));
        $db = new PDO('sqlite::memory:');
        $app->group('/api', function (\Slim\Routing\RouteCollectorProxy $g) use ($db) {
            $g->get('/health', new HealthController($db));
            $g->group('', function (\Slim\Routing\RouteCollectorProxy $auth) {
                $auth->get('/me', function ($req, $res) {
                    return (new MeController())->show($req, $res);
                });
            })->add(new RateLimitMiddleware(['bucket' => 'ip', 'limit' => 60]))
              ->add(new JwtMiddleware(['secret' => $this->jwtSecret, 'alg' => 'HS256', 'ttl' => 3600]));
        })->add(new TelegramInitDataMiddleware($this->botToken));
        return $app;
    }

    public function testHealthEndpoint(): void
    {
        $app = $this->createApp();
        $init = $this->buildInitData();
        $req = (new ServerRequestFactory())->createServerRequest('GET', '/api/health')
            ->withHeader('X-Telegram-Init-Data', $init);
        $res = $app->handle($req);
        $this->assertSame(200, $res->getStatusCode());
    }

    public function testMeWithValidInitData(): void
    {
        $app = $this->createApp();
        $init = $this->buildInitData();
        $token = JWT::encode(['uid' => 1, 'exp' => time() + 3600], $this->jwtSecret, 'HS256');
        $req = (new ServerRequestFactory())->createServerRequest('GET', '/api/me')
            ->withHeader('X-Telegram-Init-Data', $init)
            ->withHeader('Authorization', 'Bearer ' . $token);
        $res = $app->handle($req);
        $this->assertSame(200, $res->getStatusCode());
        $body = json_decode((string)$res->getBody(), true);
        $this->assertSame(['user' => ['id' => 1]], $body);
    }

    public function testMeWithInvalidInitData(): void
    {
        $app = $this->createApp();
        $init = $this->buildInitData() . 'broken';
        $token = JWT::encode(['uid' => 1, 'exp' => time() + 3600], $this->jwtSecret, 'HS256');
        $req = (new ServerRequestFactory())->createServerRequest('GET', '/api/me')
            ->withHeader('X-Telegram-Init-Data', $init)
            ->withHeader('Authorization', 'Bearer ' . $token);
        $res = $app->handle($req);
        $this->assertSame(403, $res->getStatusCode());
    }

    public function testShowWithoutTelegramUserAttribute(): void
    {
        $controller = new MeController();
        $req = (new ServerRequestFactory())->createServerRequest('GET', '/api/me');
        $res = new PsrResponse();
        $res = $controller->show($req, $res);
        $this->assertSame(403, $res->getStatusCode());
    }
}
