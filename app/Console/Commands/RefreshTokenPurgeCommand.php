<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;
use App\Domain\RefreshTokenTable;
use App\Services\Db;

class RefreshTokenPurgeCommand extends Command
{
    public string $signature = 'tokens:purge';
    public string $description = 'Remove expired refresh tokens';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $repo = new RefreshTokenTable(Db::get());
        $count = $repo->deleteExpired();
        echo "Purged {$count} expired tokens." . PHP_EOL;
        return 0;
    }
}
