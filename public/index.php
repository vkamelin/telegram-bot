<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;

require __DIR__ . '/../vendor/autoload.php';

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

// Dashboard (/admin/*) — CSRF/сессия
$app->group('/admin', function (\Slim\Routing\RouteCollectorProxy $g) use ($pdo) {
    $g->get('', [\App\Controllers\Dashboard\HomeController::class, 'index']);
    // добавляйте страницы админки здесь
})->add(new \App\Middleware\CsrfMiddleware());

// API (/api/*) — JWT + RateLimit
$app->group('/api', function (\Slim\Routing\RouteCollectorProxy $g) use ($pdo) {
    $g->post('/auth/login', [\App\Controllers\Api\AuthController::class, 'login']);
    $g->get('/users', [\App\Controllers\Api\UsersController::class, 'list']);
    $g->post('/users', [\App\Controllers\Api\UsersController::class, 'create']);
})->add(new \App\Middleware\RateLimitMiddleware($config['rate_limit']))
    ->add(new \App\Middleware\JwtMiddleware($config['jwt']));

// === Запуск ===
$app->run();
