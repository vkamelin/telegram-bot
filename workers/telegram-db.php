<?php
/**
 * Воркер отправки сообщений Telegram (версия только для БД).
 *
 * Назначение:
 * - Забирает задачи из таблицы БД telegram_messages (по приоритетам);
 * - Отправляет сообщения через Telegram Bot API с учётом лимитов RPS;
 * - Переоткладывает и помечает ошибки с ретраями.
 *
 * Основные настройки окружения:
 * - BOT_API_SERVER/BOT_LOCAL_API_HOST/BOT_LOCAL_API_PORT — параметры локального API;
 * - BOT_MAX_RPS — ограничения на скорость отправки (максимум 25 сообщений в секунду).
 */

declare(strict_types=1);

use App\Helpers\Logger;
use App\Helpers\Database;
use App\Telemetry;
use App\Config;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

require_once __DIR__ . '/../vendor/autoload.php';

$config = Config::getInstance();

try {
    if ($_ENV['BOT_API_SERVER'] === 'local') {
        $apiBaseUri = 'http://' . $_ENV['BOT_LOCAL_API_HOST'] . ':' . $_ENV['BOT_LOCAL_API_PORT'];
        $apiBaseDownloadUri = '/root/telegram-bot-api/' . $_ENV['BOT_TOKEN'];
        Request::setCustomBotApiUri($apiBaseUri, $apiBaseDownloadUri);
    }
    
    $telegram = new Telegram($_ENV['BOT_TOKEN'], $_ENV['BOT_NAME']);
    Logger::info('Процесс отправки Telegram (БД версия) запущен');
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    Logger::error("Telegram initialization failed: {$e->getMessage()}");
    exit();
}

// Максимальное количество запросов в секунду (максимум 25)
$globalMaxRps = min(25, (int)($_ENV['BOT_MAX_RPS'] ?? 25));
const MAX_ATTEMPTS = 5;

/**
 * Запускает воркер для отправки сообщений.
 */
function runWorker(): void
{
    global $globalMaxRps, $config;

    while (true) {
        $startTime = microtime(true);
        $messagesProcessed = 0;
        
        try {
            $db = Database::getInstance();
        } catch (\Exception $e) {
            Logger::error('Database connection failed: ' . $e->getMessage());
            return;
        }

        // Получаем сообщения из БД по приоритетам (2, 1, 0)
        for ($priority = 2; $priority >= 0 && $messagesProcessed < $globalMaxRps; $priority--) {
            try {
                // Выбираем сообщения для обработки (максимум 25 за раз)
                $batchSize = min(25 - $messagesProcessed, 10); // Берем по 10 за раз для батча
                
                $stmt = $db->prepare(
                    "SELECT id, method, data, priority, sent_at 
                     FROM `telegram_messages` 
                     WHERE status = 'pending' AND priority = :priority 
                     ORDER BY sent_at ASC 
                     LIMIT :limit"
                );
                $stmt->bindValue(':priority', $priority, \PDO::PARAM_INT);
                $stmt->bindValue(':limit', $batchSize, \PDO::PARAM_INT);
                $stmt->execute();
                
                $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($messages as $message) {
                    if ($messagesProcessed >= $globalMaxRps) {
                        break;
                    }
                    
                    $id = (int)$message['id'];
                    
                    // Проверяем, не обрабатывается ли уже это сообщение
                    $checkStmt = $db->prepare(
                        "UPDATE `telegram_messages` 
                         SET status = 'processing', processed_at = NOW() 
                         WHERE id = :id AND status = 'pending'"
                    );
                    $checkStmt->execute(['id' => $id]);
                    
                    if ($checkStmt->rowCount() === 0) {
                        // Сообщение уже обрабатывается другим воркером
                        continue;
                    }
                    
                    try {
                        $data = json_decode($message['data'], true, 512, JSON_THROW_ON_ERROR) ?? [];
                    } catch (Throwable $e) {
                        Logger::error("Invalid JSON for message ID {$id}: {$e->getMessage()}");
                        markAsFailed($db, $id, 'Invalid JSON data', null);
                        continue;
                    }
                    
                    $method = $message['method'];
                    $attempts = 0; // В БД версии считаем попытки по времени отправки
                    
                    // Определяем, нужно ли кодировать файл
                    $fileIndex = match ($method) {
                        'sendPhoto' => 'photo',
                        'sendVideo' => 'video',
                        'sendDocument' => 'document',
                        'sendAudio' => 'audio',
                        'sendAnimation' => 'animation',
                        'sendVoice' => 'voice',
                        'sendVideoNote' => 'video_note',
                        'sendSticker' => 'sticker',
                        default => null,
                    };
                    
                    if ($fileIndex !== null && file_exists((string)$data[$fileIndex])) {
                        $data[$fileIndex] = Request::encodeFile($data[$fileIndex]);
                    }
                    
                    try {
                        /** @var ServerResponse $response */
                        $response = Request::$method($data);
                        Logger::debug("Sent message ID {$id}: " . ($response->isOk() ? 'ok' : 'failed'));
                    } catch (\Exception $e) {
                        Logger::error("Exception sending message ID {$id}: {$e->getMessage()}");
                        $response = new ServerResponse([
                            'ok' => false,
                            'error_code' => $e->getCode(),
                            'description' => $e->getMessage(),
                        ]);
                    }

                    if ($response->isOk()) {
                        markAsSuccess($db, $id, $response);
                        if (Telemetry::enabled()) {
                            Telemetry::incrementTelegramSent();
                        }
                    } else {
                        $rawResp = $response->getRawData();
                        $reason = $rawResp['description'] ?? 'unknown';
                        if (Telemetry::enabled()) {
                            Telemetry::recordTelegramSendFailure($reason);
                        }
                        
                        // Подсчитываем количество попыток для этого сообщения
                        $attemptsStmt = $db->prepare(
                            "SELECT COUNT(*) as attempt_count 
                             FROM `telegram_messages` 
                             WHERE id = :id"
                        );
                        $attemptsStmt->execute(['id' => $id]);
                        $attempts = (int)$attemptsStmt->fetchColumn();
                        
                        if ($attempts >= MAX_ATTEMPTS) {
                            markAsFailed($db, $id, $reason, $rawResp['error_code'] ?? null);
                        } else {
                            // Возвращаем в очередь с задержкой (экспоненциальная)
                            $delay = pow(2, $attempts - 1);
                            $retryStmt = $db->prepare(
                                "UPDATE `telegram_messages` 
                                 SET status = 'pending', 
                                     sent_at = DATE_ADD(NOW(), INTERVAL :delay SECOND),
                                     error = :error,
                                     code = :code
                                 WHERE id = :id"
                            );
                            $retryStmt->execute([
                                'delay' => $delay,
                                'error' => $reason,
                                'code' => $rawResp['error_code'] ?? null,
                                'id' => $id,
                            ]);
                            Logger::info("Message ID {$id} retry scheduled in {$delay}s (attempt {$attempts})");
                        }
                    }
                    
                    $messagesProcessed++;
                }
                
            } catch (Throwable $e) {
                echo "Queue processing error: {$e->getMessage()}\n";
                Logger::error("Queue processing error: {$e->getMessage()}");
            }
        }

        if (Telemetry::enabled()) {
            // Подсчитываем размер очереди
            $queueStmt = $db->prepare("SELECT COUNT(*) FROM `telegram_messages` WHERE status = 'pending'");
            $queueStmt->execute();
            $totalQueue = (int)$queueStmt->fetchColumn();
            Telemetry::setTelegramQueueSize($totalQueue);
        }
        
        // Пауза, чтобы не превышать лимит запросов
        $elapsed = microtime(true) - $startTime;
        if ($messagesProcessed === 0 || $elapsed < 1.0) {
            $sleep = max(1_000_000 - (int)($elapsed * 1_000_000), 100_000);
            usleep($sleep);
        }
        
        if (function_exists('pcntl_signal_dispatch')) {
            pcntl_signal_dispatch();
        }
    }
}

/**
 * Помечает сообщение как успешно отправленное
 */
function markAsSuccess(\PDO $db, int $id, ServerResponse $response): void
{
    $raw = $response->getRawData();
    $msgId = $raw['result']->message_id ?? null;
    
    try {
        $stmt = $db->prepare(
            "UPDATE `telegram_messages`
             SET message_id   = :message_id,
                 status       = 'success',
                 response     = :response,
                 error        = NULL,
                 code         = NULL,
                 processed_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            'message_id' => $msgId,
            'response' => json_encode($raw, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'id' => $id,
        ]);

        // Обновляем счетчики для запланированных рассылок
        updateScheduledCounters($db, $id, 'success');
        
    } catch (\Exception $e) {
        Logger::error("Error saving success update for ID {$id}: {$e->getMessage()}");
    }
}

/**
 * Помечает сообщение как неудачное
 */
function markAsFailed(\PDO $db, int $id, string $error, ?int $code): void
{
    try {
        $stmt = $db->prepare(
            "UPDATE `telegram_messages`
             SET status       = 'failed',
                 error        = :error,
                 code         = :code,
                 processed_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            'error' => $error,
            'code' => $code,
            'id' => $id,
        ]);

        // Обновляем счетчики для запланированных рассылок
        updateScheduledCounters($db, $id, 'failed');
        
    } catch (\Exception $e) {
        Logger::error("Error saving failed update for ID {$id}: {$e->getMessage()}");
    }
}

/**
 * Обновляет счетчики для запланированных рассылок
 */
function updateScheduledCounters(\PDO $db, int $id, string $status): void
{
    try {
        $stmt = $db->prepare('SELECT scheduled_id FROM telegram_messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        $scheduledId = $row['scheduled_id'] ?? null;
        
        if ($scheduledId) {
            if ($status === 'success') {
                $db->prepare('UPDATE `telegram_scheduled_messages` SET `success_count` = `success_count` + 1 WHERE id = :sid')
                    ->execute(['sid' => $scheduledId]);
            } elseif ($status === 'failed') {
                $db->prepare('UPDATE `telegram_scheduled_messages` SET `failed_count` = `failed_count` + 1 WHERE id = :sid')
                    ->execute(['sid' => $scheduledId]);
            }

            // Если счетчики достигли общего количества, помечаем как завершенную
            $db->prepare(
                "UPDATE `telegram_scheduled_messages`
                 SET `status` = 'completed', `completed_at` = IFNULL(`completed_at`, NOW())
                 WHERE id = :sid
                   AND `status` = 'processing'
                   AND `selected_count` > 0
                   AND (`success_count` + `failed_count`) >= `selected_count`"
            )->execute(['sid' => $scheduledId]);
        }
    } catch (\Throwable $e) {
        Logger::error('Failed to update scheduled counters: ' . $e->getMessage());
    }
}

runWorker();