<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Dashboard\MessagesController;
use App\Helpers\Database;
use App\Helpers\RedisHelper;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

final class MessagesControllerSendTest extends TestCase
{
    private PDO $db;
    private MessagesController $controller;

    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'test';
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->exec('CREATE TABLE telegram_users (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, username TEXT)');
        $this->db->exec('CREATE TABLE telegram_user_groups (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $this->db->exec('CREATE TABLE telegram_user_group_user (group_id INTEGER, user_id INTEGER)');
        $this->db->exec('CREATE TABLE telegram_messages (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, method TEXT, type TEXT, data TEXT, priority INTEGER)');
        $this->db->exec("INSERT INTO telegram_users (id, user_id, username) VALUES (1,100,'alice'), (2,101,'bob'), (3,102,'carol')");
        $this->db->exec("INSERT INTO telegram_user_groups (id, name) VALUES (1,'group1')");
        $this->db->exec("INSERT INTO telegram_user_group_user (group_id, user_id) VALUES (1,1),(1,2)");
        $dbRef = new ReflectionClass(Database::class);
        $prop = $dbRef->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, $this->db);
        $redisStub = new class {
            public array $data = [];
            public function set($key, $value): bool { $this->data[$key] = $value; return true; }
            public function rPush($key, $value): bool { $this->data[$key][] = $value; return true; }
            public function del($key): int { unset($this->data[$key]); return 1; }
        };
        $redisRef = new ReflectionClass(RedisHelper::class);
        $propRedis = $redisRef->getProperty('instance');
        $propRedis->setAccessible(true);
        $propRedis->setValue(null, $redisStub);
        $this->controller = new MessagesController($this->db);
        $_SESSION = [];
    }

    private function send(array $body): void
    {
        $factory = new ServerRequestFactory();
        $req = $factory->createServerRequest('POST', '/');
        $req = $req->withParsedBody($body);
        $res = new Response();
        $this->controller->send($req, $res);
    }

    public function testSendAll(): void
    {
        $this->send(['text' => 'hi', 'mode' => 'all']);
        $rows = $this->db->query('SELECT user_id FROM telegram_messages ORDER BY user_id')->fetchAll(PDO::FETCH_COLUMN);
        $this->assertSame([100, 101, 102], array_map('intval', $rows));
    }

    public function testSendSingleByUsername(): void
    {
        $this->send(['text' => 'hi', 'mode' => 'single', 'user' => 'bob']);
        $row = $this->db->query('SELECT user_id FROM telegram_messages')->fetchColumn();
        $this->assertSame(101, (int)$row);
    }

    public function testSendSelected(): void
    {
        $this->send(['text' => 'hi', 'mode' => 'selected', 'users' => ['100', 'bob']]);
        $rows = $this->db->query('SELECT user_id FROM telegram_messages ORDER BY user_id')->fetchAll(PDO::FETCH_COLUMN);
        $this->assertSame([100, 101], array_map('intval', $rows));
    }

    public function testSendGroup(): void
    {
        $this->send(['text' => 'hi', 'mode' => 'group', 'group_id' => 1]);
        $rows = $this->db->query('SELECT user_id FROM telegram_messages ORDER BY user_id')->fetchAll(PDO::FETCH_COLUMN);
        $this->assertSame([100, 101], array_map('intval', $rows));
    }
}
