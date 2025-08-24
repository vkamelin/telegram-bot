<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;
use App\Helpers\Logger;

final class WorkerHandlerCommand extends Command
{
    public string $signature = 'worker:handler';
    public string $description = 'Handle update payload in background';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $payload = $arguments[0] ?? null;
        if ($payload === null) {
            Logger::error('Missing argument: payload');
            return 1;
        }

        if (base64_decode($payload, true) === false) {
            Logger::error('Invalid argument: payload must be base64-encoded.');
            return 1;
        }

        $handler = $_ENV['WORKER_HANDLER_PATH'] ?? dirname(__DIR__, 3) . '/workers/handler.php';
        
        Logger::info('Handling update payload', ['path' => $handler]);

        require $handler;

        return 0;
    }
}
