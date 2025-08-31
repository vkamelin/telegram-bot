<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;
use App\Helpers\Database;
use App\Helpers\Logger;
use App\Helpers\Push;
use PDO;
use Throwable;

/**
 * Переносит готовые к отправке отложенные сообщения в очередь через Push.
 */
final class ScheduledDispatchCommand extends Command
{
    public string $signature = 'scheduled:dispatch';
    public string $description = 'Поставить в очередь отложенные сообщения, срок которых наступил';
    
    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $limit = 100;
        foreach ($arguments as $arg) {
            if (str_starts_with($arg, '--limit=')) {
                $v = (int)substr($arg, 8);
                if ($v > 0) {
                    $limit = $v;
                }
            }
        }
        
        $db = Database::getInstance();
        
        // TODO: Внедрить логику полуцчения сообщений, время которых пришло, получить список пользователей для этого сообщения, и добавить в очередь классом Push.
    }
    
}
