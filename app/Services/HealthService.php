<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use App\Helpers\RedisHelper;

/**
 * Service for checking application health components.
 */
final class HealthService
{
    /**
     * Check health of database, Redis and worker.
     *
     * @return array{db: bool, redis: bool, worker: bool, status: string}
     */
    public static function check(): array
    {
        $db     = self::checkDb();
        $redis  = self::checkRedis();
        $worker = self::checkWorker();

        return [
            'db'     => $db,
            'redis'  => $redis,
            'worker' => $worker,
            'status' => ($db && $redis && $worker) ? 'ok' : 'fail',
        ];
    }

    /**
     * Check database connection.
     */
    private static function checkDb(): bool
    {
        try {
            Database::getInstance()->query('SELECT 1');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Check Redis connection.
     */
    private static function checkRedis(): bool
    {
        try {
            $redis = RedisHelper::getInstance();
            if (method_exists($redis, 'isConnected')) {
                return (bool)$redis->isConnected();
            }
            return $redis->ping() !== false;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Check whether worker process is running or lock file exists.
     */
    private static function checkWorker(): bool
    {
        try {
            $output = @shell_exec('ps aux | grep workers/telegram.php | grep -v grep');
            if (is_string($output) && trim($output) !== '') {
                return true;
            }
        } catch (\Throwable) {
            // ignore
        }

        $lockFile = __DIR__ . '/../../storage/worker.lock';
        if (is_file($lockFile)) {
            $mtime = filemtime($lockFile);
            if ($mtime !== false && (time() - $mtime) < 60) {
                return true;
            }
        }

        return false;
    }
}
