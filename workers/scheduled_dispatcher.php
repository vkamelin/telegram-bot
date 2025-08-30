<?php

declare(strict_types=1);

use App\Console\Kernel;
use App\Helpers\Logger;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

// Simple looped runner for scheduled:dispatch console command
// Moves due records from telegram_scheduled_messages to Redis queue via Push helper

$limit = (int)($_ENV['SCHEDULED_DISPATCH_LIMIT'] ?? 100);
if ($limit <= 0) {
    $limit = 100;
}

Logger::info('Scheduled dispatcher worker started', ['limit' => $limit]);

$kernel = new Kernel();

while (true) {
    $started = microtime(true);

    try {
        // Reuse console kernel to execute the command
        $kernel->handle(['run', 'scheduled:dispatch', "--limit={$limit}"]);
    } catch (Throwable $e) {
        Logger::error('Scheduled dispatcher iteration failed: ' . $e->getMessage());
    }

    // Keep a gentle cadence (at most 1 run per second)
    $elapsed = microtime(true) - $started;
    if ($elapsed < 1.0) {
        usleep((int)((1.0 - $elapsed) * 1_000_000));
    }
}
