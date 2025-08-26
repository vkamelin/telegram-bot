<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\Database;
use App\Helpers\Push;
use App\Helpers\RedisHelper;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PushMediaGroupTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'test';

        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->exec('CREATE TABLE telegram_messages (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, method TEXT, type TEXT, data TEXT, priority INTEGER)');

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
    }

    public function testMediaGroupQueued(): void
    {
        $media = [
            ['type' => 'photo', 'media' => 'https://example.com/a.jpg', 'caption' => 'One'],
            ['type' => 'photo', 'media' => 'https://example.com/b.jpg'],
        ];

        $result = Push::mediaGroup(321, $media);
        $this->assertTrue($result);

        $row = $this->db->query('SELECT user_id, method, data FROM telegram_messages')->fetch();
        $this->assertSame(321, (int)$row['user_id']);
        $this->assertSame('sendMediaGroup', $row['method']);
        $data = json_decode($row['data'], true);
        $this->assertSame('https://example.com/a.jpg', $data['media'][0]['media']);
        $this->assertSame('html', $data['media'][0]['parse_mode']);
        $this->assertArrayNotHasKey('parse_mode', $data['media'][1]);
    }
}
