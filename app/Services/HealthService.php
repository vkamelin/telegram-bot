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
     * Check whether required worker processes are running under Supervisor.
     */
    private static function checkWorker(): bool
    {
        $patterns = [
            'gpt' => '/^gpt:gpt-\d+\s+RUNNING\b/',
            'tg'  => '/^tg:tg-\d+\s+RUNNING\b/',
            'lp'  => '/^lp\s+RUNNING\b/',
        ];

        try {
            $command   = 'supervisorctl status ' . implode(' ', array_keys($patterns));
            $output    = [];
            $returnVar = 0;
            @exec($command, $output, $returnVar);
            if ($returnVar !== 0) {
                return false;
            }

            foreach ($patterns as $pattern) {
                $found = false;
                foreach ($output as $line) {
                    if (preg_match($pattern, $line)) {
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
