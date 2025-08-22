<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Domain\RefreshTokenTable;
use App\Services\Db;

$repo = new RefreshTokenTable(Db::get());
$count = $repo->deleteExpired();
echo "Purged {$count} expired tokens." . PHP_EOL;
