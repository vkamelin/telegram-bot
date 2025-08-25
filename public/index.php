<?php
declare(strict_types=1);

use App\Helpers\Database;
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
$db = Database::getInstance();

// === Slim App ===
$app = AppFactory::create();
$app->add(new RequestIdMiddleware());
$app->add(new RequestSizeLimitMiddleware($config['request_size_limit']));
$app->addBodyParsingMiddleware();
$app->add(new SecurityHeadersMiddleware([
    'cors' => $config['cors'],
    'csp' => [
        'script' => 'https://code.jquery.com, https://cdn.jsdelivr.net, https://cdn.datatables.net, https://cdn.tailwindcss.com, https://cdnjs.cloudflare.com',
        'style' => 'https://fonts.googleapis.com, https://cdn.jsdelivr.net, https://cdn.datatables.net, https://cdn.tailwindcss.com',
        'font' => 'https://fonts.gstatic.com',
        'connect' => 'https://cdn.datatables.net',
    ],
    'x_frame_options' => 'DENY',
]));

// === Error handler (RFC7807) ===
$app->add(new \App\Middleware\ErrorMiddleware($config['debug']));

// === Группы маршрутов ===

// Dashboard (/dashboard/*) — session + CSRF + auth
$app->group('/dashboard', function (\Slim\Routing\RouteCollectorProxy $g) use ($db) {
    $g->get('/login', [\App\Controllers\Dashboard\AuthController::class, 'showLogin']);
    $g->post('/login', [\App\Controllers\Dashboard\AuthController::class, 'login']);
    $g->post('/logout', [\App\Controllers\Dashboard\AuthController::class, 'logout']);

    $g->group('', function (\Slim\Routing\RouteCollectorProxy $auth) use ($db) {
        $auth->get('', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\HomeController($db))->index($req, $res));
        $auth->get('/messages', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\MessagesController($db))->index($req, $res));
        $auth->post('/messages/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\MessagesController($db))->data($req, $res));
        $auth->post('/messages/{id}/resend', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\MessagesController($db))->resend($req, $res));
        $auth->get('/messages/{id}/response', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\MessagesController($db))->download($req, $res));
        $auth->get('/pre-checkout', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PreCheckoutController($db))->index($req, $res));
        $auth->post('/pre-checkout/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PreCheckoutController($db))->data($req, $res));
        $auth->get('/shipping', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ShippingQueriesController($db))->index($req, $res));
        $auth->post('/shipping/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ShippingQueriesController($db))->data($req, $res));
        $auth->get('/invoices/create', [\App\Controllers\Dashboard\InvoicesController::class, 'create']);
        $auth->post('/invoices', [\App\Controllers\Dashboard\InvoicesController::class, 'store']);
        $auth->get('/updates', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\UpdatesController($db))->index($req, $res));
        $auth->post('/updates/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\UpdatesController($db))->data($req, $res));
        $auth->get('/updates/{id}', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\UpdatesController($db))->show($req, $res));
        $auth->get('/sessions', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\SessionsController($db))->index($req, $res));
        $auth->post('/sessions/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\SessionsController($db))->data($req, $res));
        $auth->get('/tokens', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TokensController($db))->index($req, $res));
        $auth->post('/tokens/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TokensController($db))->data($req, $res));
        $auth->post('/tokens/{id}/revoke', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TokensController($db))->revoke($req, $res));
        $auth->get('/tg-users', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TgUsersController($db))->index($req, $res));
        $auth->post('/tg-users/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TgUsersController($db))->data($req, $res));
        $auth->get('/tg-users/{id}', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TgUsersController($db))->view($req, $res));
        $auth->get('/join-requests', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ChatJoinRequestsController($db))->index($req, $res));
        $auth->post('/join-requests/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ChatJoinRequestsController($db))->data($req, $res));
        $auth->get('/join-requests/{chat_id}/{user_id}', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ChatJoinRequestsController($db))->view($req, $res));
        $auth->post('/join-requests/{chat_id}/{user_id}/approve', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ChatJoinRequestsController($db))->approve($req, $res));
        $auth->post('/join-requests/{chat_id}/{user_id}/decline', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ChatJoinRequestsController($db))->decline($req, $res));
        $auth->get('/chat-members', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ChatMembersController($db))->index($req, $res));
        $auth->post('/chat-members/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ChatMembersController($db))->data($req, $res));
        $auth->get('/users', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->index($req, $res));
        $auth->post('/users/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->data($req, $res));
        $auth->get('/users/create', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->create($req, $res));
        $auth->post('/users', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->store($req, $res));
        $auth->get('/users/{id}/edit', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->edit($req, $res));
        $auth->post('/users/{id}', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->update($req, $res));
        $auth->get('/scheduled', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ScheduledController($db))->index($req, $res));
        $auth->post('/scheduled/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ScheduledController($db))->data($req, $res));
        $auth->post('/scheduled/{id}/send-now', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ScheduledController($db))->sendNow($req, $res));
        $auth->post('/scheduled/{id}/delete', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ScheduledController($db))->delete($req, $res));
        $auth->get('/system', [\App\Controllers\Dashboard\SystemController::class, 'index']);
        // добавляйте страницы админки здесь
    })->add(new \App\Middleware\AuthMiddleware());
})->add(new \App\Middleware\CsrfMiddleware())
  ->add(new \App\Middleware\SessionMiddleware());

// API (/api/*)
$app->group('/api', function (\Slim\Routing\RouteCollectorProxy $g) use ($db, $config) {
    $g->get('/health', new \App\Controllers\Api\HealthController($db));
    $g->post('/auth/login', fn(Req $req, Res $res) => (new \App\Controllers\Api\AuthController($db, $config['jwt']))->login($req, $res));

    $g->group('', function (\Slim\Routing\RouteCollectorProxy $auth) use ($db) {
          $auth->get('/me', function (Req $req, Res $res): Res {
              return (new \App\Controllers\Api\MeController())->show($req, $res);
          });

        $auth->get('/users', fn(Req $req, Res $res) => (new \App\Controllers\Api\UsersController($db))->list($req, $res));
        $auth->post('/users', fn(Req $req, Res $res) => (new \App\Controllers\Api\UsersController($db))->create($req, $res));
    })->add(new \App\Middleware\RateLimitMiddleware($config['rate_limit']))
      ->add(new \App\Middleware\JwtMiddleware($config['jwt']));
})->add(new \App\Middleware\TelegramInitDataMiddleware($config['bot_token'] ?: ''));

// === Запуск ===
$app->run();
