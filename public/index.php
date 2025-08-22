<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$config = require __DIR__ . '/../app/Config/config.php';

// === PDO ===
$pdo = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['pass'], $config['db']['opts']);

// === Slim App ===
$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// === Error handler (RFC7807) ===
$app->add(new \App\Middleware\ErrorMiddleware($config['debug']));

// === CORS для API ===
$app->options('/{routes:.+}', fn(Req $r, Res $w)=>$w);
$app->add(function (Req $req, $handler) use ($config) {
    $res = $handler->handle($req);
    $origin = $req->getHeaderLine('Origin');
    if ($origin && (empty($config['cors']['origins']) || in_array($origin, $config['cors']['origins'], true))) {
        return $res
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', $config['cors']['headers'])
            ->withHeader('Access-Control-Allow-Methods', $config['cors']['methods']);
    }
    return $res;
});

// === Группы маршрутов ===

// Dashboard (/dashboard/*) — CSRF/сессия
$app->group('/dashboard', function (\Slim\Routing\RouteCollectorProxy $g) use ($pdo) {
    $g->get('', [\App\Controllers\Dashboard\HomeController::class, 'index']);
    // добавляйте страницы админки здесь
})->add(new \App\Middleware\CsrfMiddleware());

// API (/api/*)
$app->group('/api', function (\Slim\Routing\RouteCollectorProxy $g) use ($pdo, $config) {
    $g->get('/health', fn(Req $req, Res $res): Res => \App\Helpers\Response::json($res, 200, ['status' => 'ok']));
    $g->post('/auth/login', [\App\Controllers\Api\AuthController::class, 'login']);

    $g->group('', function (\Slim\Routing\RouteCollectorProxy $auth) use ($pdo) {
          $auth->get('/me', function (Req $req, Res $res) use ($pdo): Res {
              return (new \App\Controllers\Api\MeController($pdo))->show($req, $res);
          });

          $auth->get('/items', [\App\Controllers\Api\ItemsController::class, 'list']);

          $auth->post('/orders', [\App\Controllers\Api\OrdersController::class, 'create']);

        $auth->get('/users', [\App\Controllers\Api\UsersController::class, 'list']);
        $auth->post('/users', [\App\Controllers\Api\UsersController::class, 'create']);
    })->add(new \App\Middleware\RateLimitMiddleware($config['rate_limit']))
      ->add(new \App\Middleware\JwtMiddleware($config['jwt']));
})->add(new \App\Middleware\TelegramInitDataMiddleware($config['bot_token'] ?: ''));

// === Запуск ===
$app->run();
