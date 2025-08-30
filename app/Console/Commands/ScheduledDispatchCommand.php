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

        $stmt = $db->prepare(
            "SELECT id, user_id, method, `type`, `data`, priority FROM `telegram_scheduled_messages` WHERE `send_after` <= NOW() AND `status` = 'pending' ORDER BY `id` ASC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $processed = 0;
        foreach ($rows as $row) {
            try {
                // Lock row
                $lock = $db->prepare("UPDATE `telegram_scheduled_messages` SET `status` = 'processing', `started_at` = NOW() WHERE `id` = :id AND `status` = 'pending'");
                $lock->execute(['id' => $row['id']]);
                if ($lock->rowCount() === 0) {
                    continue; // already taken
                }

                $payload = [];
                try {
                    $payload = json_decode((string)$row['data'], true, 512, JSON_THROW_ON_ERROR);
                } catch (Throwable $e) {
                    Logger::error('Invalid scheduled payload', ['id' => $row['id'], 'exception' => $e]);
                    // rollback to pending for manual fix
                    $db->prepare("UPDATE `telegram_scheduled_messages` SET `status` = 'pending' WHERE `id` = :id")
                        ->execute(['id' => $row['id']]);
                    continue;
                }

                $ok = Push::custom(
                    (string)$row['method'],
                    is_array($payload) ? $payload : [],
                    isset($row['user_id']) ? (int)$row['user_id'] : null,
                    (string)$row['type'],
                    (int)$row['priority'],
                    null // immediate enqueue
                );

                if (!$ok) {
                    // Roll back to pending to retry later
                    $db->prepare("UPDATE `telegram_scheduled_messages` SET `status` = 'pending' WHERE `id` = :id")
                        ->execute(['id' => $row['id']]);
                    continue;
                }

                $processed++;
            } catch (Throwable $e) {
                Logger::error('Failed to dispatch scheduled', ['id' => $row['id'] ?? null, 'exception' => $e]);
                try {
                    $db->prepare("UPDATE `telegram_scheduled_messages` SET `status` = 'pending' WHERE `id` = :id")
                        ->execute(['id' => $row['id']]);
                } catch (Throwable) {
                    // ignore
                }
            }
        }

        echo "Scheduled dispatched: {$processed}" . PHP_EOL;
        return 0;
    }
}

