<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use Dotenv\Dotenv;
use App\Middleware\RequestIdMiddleware;
use App\Middleware\RequestSizeLimitMiddleware;
use App\Middleware\SecurityHeadersMiddleware;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$config = require __DIR__ . '/../app/Config/config.php';

// === PDO ===
$pdo = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['pass'], $config['db']['opts']);

// === Slim App ===
$app = AppFactory::create();
$app->add(new RequestIdMiddleware());
$app->add(new RequestSizeLimitMiddleware($config['request_size_limit']));
$app->addBodyParsingMiddleware();
$app->add(new SecurityHeadersMiddleware([
    'cors' => $config['cors'],
    'csp' => [],
    'x_frame_options' => 'DENY',
]));

// === Error handler (RFC7807) ===
$app->add(new \App\Middleware\ErrorMiddleware($config['debug']));

// === Группы маршрутов ===

// Dashboard (/dashboard/*) — session + CSRF + auth
$app->group('/dashboard', function (\Slim\Routing\RouteCollectorProxy $g) use ($pdo) {
    $g->get('/login', [\App\Controllers\Dashboard\AuthController::class, 'showLogin']);
    $g->post('/login', [\App\Controllers\Dashboard\AuthController::class, 'login']);
    $g->post('/logout', [\App\Controllers\Dashboard\AuthController::class, 'logout']);

    $g->group('', function (\Slim\Routing\RouteCollectorProxy $auth) {
        $auth->get('', [\App\Controllers\Dashboard\HomeController::class, 'index']);
        $auth->get('/messages', [\App\Controllers\Dashboard\MessagesController::class, 'index']);
        $auth->post('/messages/data', [\App\Controllers\Dashboard\MessagesController::class, 'data']);
        $auth->post('/messages/{id}/resend', [\App\Controllers\Dashboard\MessagesController::class, 'resend']);
        $auth->get('/messages/{id}/response', [\App\Controllers\Dashboard\MessagesController::class, 'download']);
        // добавляйте страницы админки здесь
    })->add(new \App\Middleware\AuthMiddleware());
})->add(new \App\Middleware\CsrfMiddleware())
  ->add(new \App\Middleware\SessionMiddleware());

// API (/api/*)
$app->group('/api', function (\Slim\Routing\RouteCollectorProxy $g) use ($pdo, $config) {
    $g->get('/health', new \App\Controllers\Api\HealthController($pdo));
    $g->post('/auth/login', [\App\Controllers\Api\AuthController::class, 'login']);

    $g->group('', function (\Slim\Routing\RouteCollectorProxy $auth) {
          $auth->get('/me', function (Req $req, Res $res): Res {
              return (new \App\Controllers\Api\MeController())->show($req, $res);
          });

        $auth->get('/users', [\App\Controllers\Api\UsersController::class, 'list']);
        $auth->post('/users', [\App\Controllers\Api\UsersController::class, 'create']);
    })->add(new \App\Middleware\RateLimitMiddleware($config['rate_limit']))
      ->add(new \App\Middleware\JwtMiddleware($config['jwt']));
})->add(new \App\Middleware\TelegramInitDataMiddleware($config['bot_token'] ?: ''));

// === Запуск ===
$app->run();
