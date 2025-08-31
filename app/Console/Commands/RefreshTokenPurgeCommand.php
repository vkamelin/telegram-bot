<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;
use App\Helpers\Database;
use App\Helpers\RefreshTokenService;

class RefreshTokenPurgeCommand extends Command
{
    public string $signature = 'tokens:purge';
    public string $description = 'Remove expired refresh tokens';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $svc = new RefreshTokenService(Database::getInstance());
        $count = $svc->purgeExpired();
        echo "Purged {$count} expired tokens." . PHP_EOL;
        return 0;
    }
}
