<?php

declare(strict_types=1);

namespace Longman\TelegramBot {
    class Request
    {
        public static array $calls = [];
        public static function approveChatJoinRequest(array $params): object
        {
            self::$calls[] = ['approve', $params];
            return (object)['ok' => true];
        }
        public static function declineChatJoinRequest(array $params): object
        {
            self::$calls[] = ['decline', $params];
            return (object)['ok' => true];
        }
    }
}

namespace Tests\Unit {
    use App\Controllers\Dashboard\ChatJoinRequestsController;
    use PDO;
    use PHPUnit\Framework\TestCase;
    use Slim\Psr7\Factory\ServerRequestFactory;
    use Slim\Psr7\Response;

    final class ChatJoinRequestsControllerTest extends TestCase
    {
        private PDO $pdo;
        private ChatJoinRequestsController $controller;

        protected function setUp(): void
        {
            $this->pdo = new PDO('sqlite::memory:');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec('CREATE TABLE chat_join_requests (chat_id INTEGER, user_id INTEGER, bio TEXT, invite_link TEXT, requested_at TEXT, status TEXT, decided_at TEXT, decided_by INTEGER, PRIMARY KEY(chat_id, user_id))');
            $this->pdo->exec('CREATE TABLE telegram_users (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, username TEXT, first_name TEXT, last_name TEXT)');
            $this->pdo->exec("INSERT INTO telegram_users (user_id, username, first_name, last_name) VALUES (1, 'john', 'John', 'Doe')");
            $this->pdo->exec("INSERT INTO chat_join_requests (chat_id, user_id, bio, invite_link, requested_at, status) VALUES (100, 1, 'bio', NULL, '2024-01-01 00:00:00', 'pending')");
            $this->controller = new ChatJoinRequestsController($this->pdo);
            $_SESSION = ['user_id' => 99];
        }

        public function testDataReturnsJson(): void
        {
            $factory = new ServerRequestFactory();
            $req = $factory->createServerRequest('POST', '/');
            $req = $req->withParsedBody(['draw' => 1, 'start' => 0, 'length' => 10]);
            $res = new Response();
            $res = $this->controller->data($req, $res);
            $payload = json_decode((string)$res->getBody(), true);
            $this->assertSame(1, $payload['draw']);
            $this->assertSame(1, $payload['recordsTotal']);
            $this->assertSame('john', $payload['data'][0]['username']);
        }

        public function testApproveAndDeclineUpdateStatus(): void
        {
            $factory = new ServerRequestFactory();
            $req = $factory->createServerRequest('POST', '/');
            $res = new Response();
            $this->controller->approve($req, $res, ['chat_id' => 100, 'user_id' => 1]);
            $row = $this->pdo->query('SELECT status, decided_by FROM chat_join_requests WHERE chat_id = 100 AND user_id = 1')->fetch();
            $this->assertSame('approved', $row['status']);
            $this->assertSame(99, (int)$row['decided_by']);

            $this->pdo->exec("INSERT INTO chat_join_requests (chat_id, user_id, bio, invite_link, requested_at, status) VALUES (100, 2, '', NULL, '2024-01-01 00:00:00', 'pending')");
            $req2 = $factory->createServerRequest('POST', '/');
            $res2 = new Response();
            $this->controller->decline($req2, $res2, ['chat_id' => 100, 'user_id' => 2]);
            $row2 = $this->pdo->query('SELECT status FROM chat_join_requests WHERE chat_id = 100 AND user_id = 2')->fetch();
            $this->assertSame('declined', $row2['status']);

            $this->assertSame([
                ['approve', ['chat_id' => 100, 'user_id' => 1]],
                ['decline', ['chat_id' => 100, 'user_id' => 2]],
            ], \Longman\TelegramBot\Request::$calls);
        }
    }
}
