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

        // Use schema detection to avoid noisy warnings in logs
        $hasTargeting = $this->columnExists($db, 'telegram_scheduled_messages', 'target_type')
            && $this->columnExists($db, 'telegram_scheduled_messages', 'target_group_id');

        if ($hasTargeting) {
            $stmt = $db->prepare(
                "SELECT id, user_id, method, `type`, `data`, priority, target_type, target_group_id
                   FROM `telegram_scheduled_messages`
                  WHERE `send_after` <= NOW() AND `status` = 'pending'
                  ORDER BY `id` ASC
                  LIMIT :limit"
            );
        } else {
            $stmt = $db->prepare(
                "SELECT id, user_id, method, `type`, `data`, priority
                   FROM `telegram_scheduled_messages`
                  WHERE `send_after` <= NOW() AND `status` = 'pending'
                  ORDER BY `id` ASC
                  LIMIT :limit"
            );
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (!$hasTargeting) {
            $rows = array_map(static function ($r) {
                $r['target_type'] = null;
                $r['target_group_id'] = null;
                return $r;
            }, $rows ?: []);
        }

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

                // Determine recipients based on targeting
                $recipients = [];
                $targetType = (string)($row['target_type'] ?? '');

                if ($row['user_id'] !== null && $targetType === '') {
                    // Legacy single-user scheduled message
                    $recipients = [(int)$row['user_id']];
                } elseif ($targetType === 'selected') {
                    $getUsers = $db->prepare('SELECT target_user_id FROM telegram_scheduled_targets WHERE scheduled_id = :sid');
                    $getUsers->execute(['sid' => $row['id']]);
                    $recipients = array_map('intval', $getUsers->fetchAll(PDO::FETCH_COLUMN));
                } elseif ($targetType === 'group') {
                    $gid = (int)($row['target_group_id'] ?? 0);
                    if ($gid > 0) {
                        $q = $db->prepare('SELECT tu.user_id FROM telegram_user_group_user ugu JOIN telegram_users tu ON tu.id = ugu.user_id WHERE ugu.group_id = :gid AND tu.is_user_banned = 0 AND tu.is_bot_banned = 0');
                        $q->execute(['gid' => $gid]);
                        $recipients = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
                    }
                } elseif ($targetType === 'all') {
                    $q = $db->query('SELECT user_id FROM telegram_users WHERE is_user_banned = 0 AND is_bot_banned = 0');
                    $recipients = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
                }

                $recipients = array_values(array_unique(array_filter($recipients, static fn($v) => is_int($v) || ctype_digit((string)$v))));

                // Update selected_count for reporting
                $db->prepare('UPDATE `telegram_scheduled_messages` SET `selected_count` = :cnt WHERE `id` = :id')
                    ->execute(['cnt' => count($recipients), 'id' => $row['id']]);

                if (!$recipients) {
                    // Nothing to send; return to pending to allow manual fix or cancel
                    $db->prepare("UPDATE `telegram_scheduled_messages` SET `status` = 'pending' WHERE `id` = :id")
                        ->execute(['id' => $row['id']]);
                    continue;
                }

                $okAll = true;
                foreach ($recipients as $uid) {
                    $perPayload = is_array($payload) ? $payload : [];
                    // Ensure chat_id matches current recipient; override if present
                    $perPayload['chat_id'] = (int)$uid;

                    $ok = Push::custom(
                        (string)$row['method'],
                        $perPayload,
                        (int)$uid,
                        (string)$row['type'],
                        (int)$row['priority'],
                        null,
                        (int)$row['id'] // link to scheduled batch
                    );
                    $okAll = $okAll && $ok;
                }

                if (!$okAll) {
                    // If some failed to enqueue, roll back status to pending to retry later
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

    /**
     * Checks if a column exists in the given table.
     */
    private function columnExists(PDO $db, string $table, string $column): bool
    {
        try {
            $stmt = $db->prepare("SHOW COLUMNS FROM `{$table}` LIKE :col");
            $stmt->execute(['col' => $column]);
            return (bool)$stmt->fetchColumn();
        } catch (Throwable) {
            return false;
        }
    }
