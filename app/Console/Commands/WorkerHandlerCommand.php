<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;

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
            echo 'Missing argument: base64-encoded payload required.' . PHP_EOL;
            return 1;
        }

        if (base64_decode($payload, true) === false) {
            echo 'Invalid argument: payload must be base64-encoded.' . PHP_EOL;
            return 1;
        }

        $handler = $_ENV['WORKER_HANDLER_PATH'] ?? dirname(__DIR__, 3) . '/workers/handler.php';

        require $handler;

        return 0;
    }
}
