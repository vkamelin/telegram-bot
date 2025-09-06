<?php
declare(strict_types=1);

use App\Helpers\Database;
use App\Middleware\RequestIdMiddleware;
use App\Middleware\RequestSizeLimitMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Message\ServerRequestInterface as Req;
use Slim\Factory\AppFactory;

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
        $auth->get('/messages/create', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\MessagesController($db))->create($req, $res));
        $auth->post('/messages/send', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\MessagesController($db))->send($req, $res));
        $auth->post('/messages/{id}/resend', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\MessagesController($db))->resend($req, $res, $args));
        $auth->get('/messages/{id}/response', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\MessagesController($db))->download($req, $res, $args));
        $auth->get('/files', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\FilesController($db, new \App\Helpers\FileService($db)))->index($req, $res));
        $auth->post('/files/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\FilesController($db, new \App\Helpers\FileService($db)))->data($req, $res));
        $auth->get('/files/create', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\FilesController($db, new \App\Helpers\FileService($db)))->create($req, $res));
        $auth->post('/files', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\FilesController($db, new \App\Helpers\FileService($db)))->store($req, $res));
        $auth->get('/files/{id}', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\FilesController($db, new \App\Helpers\FileService($db)))->show($req, $res, $args));
        $auth->get('/pre-checkout', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PreCheckoutController($db))->index($req, $res));
        $auth->post('/pre-checkout/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PreCheckoutController($db))->data($req, $res));
        $auth->get('/shipping', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ShippingQueriesController($db))->index($req, $res));
        $auth->post('/shipping/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ShippingQueriesController($db))->data($req, $res));
        $auth->get('/invoices/create', [\App\Controllers\Dashboard\InvoicesController::class, 'create']);
        $auth->post('/invoices', [\App\Controllers\Dashboard\InvoicesController::class, 'store']);
        $auth->get('/updates', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\UpdatesController($db))->index($req, $res));
        $auth->post('/updates/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\UpdatesController($db))->data($req, $res));
        $auth->get('/updates/{id}', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\UpdatesController($db))->show($req, $res, $args));
        $auth->get('/sessions', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\SessionsController($db))->index($req, $res));
        $auth->post('/sessions/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\SessionsController($db))->data($req, $res));
        $auth->get('/tokens', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TokensController($db))->index($req, $res));
        $auth->post('/tokens/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TokensController($db))->data($req, $res));
        $auth->post('/tokens/{id}/revoke', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\TokensController($db))->revoke($req, $res, $args));
        $auth->get('/tg-users', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TgUsersController($db))->index($req, $res));
        $auth->post('/tg-users/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TgUsersController($db))->data($req, $res));
        $auth->post('/tg-users/search', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\TgUsersController($db))->search($req, $res));
        $auth->get('/tg-users/{id}', fn(Req $req, Res $res, array $args) => (new \\App\\Controllers\\Dashboard\\TgUsersController($db))->view($req, $res, $args));
        $auth->get('/tg-users/{id}/chat', fn(Req $req, Res $res, array $args) => (new \\App\\Controllers\\Dashboard\\TgUsersController($db))->chat($req, $res, $args));
        ->get('/tg-groups', fn(Req , Res ) => (new \App\Controllers\Dashboard\TgGroupsController())->index(, ));
        ->get('/tg-groups', fn(Req , Res ) => (new \App\Controllers\Dashboard\TgGroupsController())->index(, ));
        ->post('/tg-groups', fn(Req , Res ) => (new \App\Controllers\Dashboard\TgGroupsController())->store(, ));
        ->map(['GET','POST'], '/tg-groups/{id}', fn(Req , Res , array ) => (new \App\Controllers\Dashboard\TgGroupsController())->view(, , ));
        ->post('/tg-groups/{id}/add-user', fn(Req , Res , array ) => (new \App\Controllers\Dashboard\TgGroupsController())->addUser(, , ));
        ->post('/tg-groups/{id}/remove-user', fn(Req , Res , array ) => (new \App\Controllers\Dashboard\TgGroupsController())->removeUser(, , ));
        $auth->get('/users', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->index($req, $res));
        $auth->post('/users/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->data($req, $res));
        $auth->get('/users/create', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->create($req, $res));
        $auth->post('/users', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PanelUsersController($db))->store($req, $res));
        $auth->get('/users/{id}/edit', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\PanelUsersController($db))->edit($req, $res, $args));
        $auth->post('/users/{id}', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\PanelUsersController($db))->update($req, $res, $args));
        $auth->get('/scheduled', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ScheduledController($db))->index($req, $res));
        $auth->post('/scheduled/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ScheduledController($db))->data($req, $res));
        $auth->get('/scheduled/{id:\\d+}', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\ScheduledController($db))->show($req, $res, $args));
        $auth->post('/scheduled/{id:\\d+}/messages', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\ScheduledController($db))->messages($req, $res, $args));
        $auth->get('/scheduled/{id}/edit', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\ScheduledController($db))->edit($req, $res, $args));
        $auth->post('/scheduled/{id}/update', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\ScheduledController($db))->update($req, $res, $args));
        $auth->post('/scheduled/{id}/cancel', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\ScheduledController($db))->cancel($req, $res, $args));
        $auth->post('/scheduled/{id}/send-now', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\ScheduledController($db))->sendNow($req, $res, $args));
        $auth->post('/scheduled/{id}/delete', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\ScheduledController($db))->delete($req, $res, $args));
        $auth->get('/system', [\App\Controllers\Dashboard\SystemController::class, 'index']);
        $auth->get('/logs', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\LogsController())->index($req, $res));
        $auth->post('/logs/files', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\LogsController())->files($req, $res));
        $auth->post('/logs/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\LogsController())->data($req, $res));
        $auth->get('/logs/view', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\LogsController())->show($req, $res));
        // добавляйте страницы админки здесь
        // UTM report
        $auth->get('/utm', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\UtmController($db))->index($req, $res));
        $auth->post('/utm', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\UtmController($db))->index($req, $res));

        // Promo codes dashboard
        $auth->get('/promo-codes', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PromoCodesController($db))->index($req, $res));
        $auth->get('/promo-codes/upload', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PromoCodesController($db))->upload($req, $res));
        $auth->post('/promo-codes/upload', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PromoCodesController($db))->uploadHandle($req, $res));
        $auth->post('/promo-codes/{id}/issue', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\PromoCodesController($db))->issue($req, $res, $args));
        $auth->get('/promo-codes/issues', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PromoCodesController($db))->issues($req, $res));
        $auth->get('/promo-codes/batches', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PromoCodesController($db))->batches($req, $res));
        $auth->get('/promo-codes/issues/export', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\PromoCodesController($db))->exportIssuesCsv($req, $res));
    })->add(new \App\Middleware\AuthMiddleware());
})->add(new \App\Middleware\CsrfMiddleware())
  ->add(new \App\Middleware\SessionMiddleware());

// Дополнительные маршруты панели (добавленные отдельно)
$app->group('/dashboard', function (\Slim\Routing\RouteCollectorProxy $g) use ($db) {
    $g->post('/updates/{id}/reply', fn(Req $req, Res $res, array $args) => (new \App\Controllers\Dashboard\UpdatesController($db))->reply($req, $res, $args));
    // Referrals report
    $g->get('/referrals', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ReferralsController($db))->index($req, $res));
    $g->post('/referrals/data', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ReferralsController($db))->data($req, $res));
    $g->post('/referrals/grouped', fn(Req $req, Res $res) => (new \App\Controllers\Dashboard\ReferralsController($db))->grouped($req, $res));
})
  ->add(new \App\Middleware\AuthMiddleware())
  ->add(new \App\Middleware\CsrfMiddleware())
  ->add(new \App\Middleware\SessionMiddleware());

// API (/api/*)
$app->group('/api', function (\Slim\Routing\RouteCollectorProxy $g) use ($db, $config) {
    $g->get('/health', new \App\Controllers\Api\HealthController($db));
    $g->post('/auth/login', fn(Req $req, Res $res) => (new \App\Controllers\Api\AuthController($db, $config['jwt']))->login($req, $res));
    $g->post('/auth/refresh', fn(Req $req, Res $res) => (new \App\Controllers\Api\AuthController($db, $config['jwt']))->refresh($req, $res));

    $g->group('', function (\Slim\Routing\RouteCollectorProxy $auth) use ($db) {
          $auth->get('/me', function (Req $req, Res $res): Res {
              return (new \App\Controllers\Api\MeController())->show($req, $res);
          });

        $auth->get('/users', fn(Req $req, Res $res) => (new \App\Controllers\Api\UsersController($db))->list($req, $res));
        $auth->post('/users', fn(Req $req, Res $res) => (new \App\Controllers\Api\UsersController($db))->create($req, $res));

        // Promo codes
        $auth->post('/promo-codes/upload', fn(Req $req, Res $res) => (new \App\Controllers\Api\PromoCodeController($db))->upload($req, $res));
        $auth->get('/promo-codes', fn(Req $req, Res $res) => (new \App\Controllers\Api\PromoCodeController($db))->listCodes($req, $res));
        $auth->post('/promo-codes/issue', fn(Req $req, Res $res) => (new \App\Controllers\Api\PromoCodeController($db))->issue($req, $res));
        $auth->get('/promo-code-issues', fn(Req $req, Res $res) => (new \App\Controllers\Api\PromoCodeController($db))->issues($req, $res));
        $auth->get('/promo-code-batches', fn(Req $req, Res $res) => (new \App\Controllers\Api\PromoCodeController($db))->batches($req, $res));
    })->add(new \App\Middleware\RateLimitMiddleware($config['rate_limit']))
      ->add(new \App\Middleware\JwtMiddleware($config['jwt']));
})->add(new \App\Middleware\TelegramInitDataMiddleware($config['bot_token'] ?: ''));

// === Запуск ===
$app->run();

