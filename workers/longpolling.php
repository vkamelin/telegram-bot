<?php

declare(strict_types=1);

use App\Helpers\Logger;
use App\Helpers\Database;
use App\Helpers\RedisHelper;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use App\Telegram\UpdateHelper;
use App\Telegram\UpdateFilter;

require_once __DIR__ . '/../vendor/autoload.php';

$db = Database::getInstance();

try {
    $redis = RedisHelper::getInstance();
} catch (\RedisException $e) {
    Logger::error('Redis connection failed: ' . $e->getMessage());
    exit();
}

try {
    if ($_ENV['TELEGRAM_API_SERVER'] === 'local') {
        $apiBaseUri = 'http://' . $_ENV['TELEGRAM_LOCAL_API_HOST'] . ':' . $_ENV['TELEGRAM_LOCAL_API_PORT'];
        $apiBaseDownloadUri = '/root/telegram-bot-api/' . $_ENV['TELEGRAM_BOT_TOKEN'];
        Request::setCustomBotApiUri($apiBaseUri, $apiBaseDownloadUri);
    }
    
    $telegram = new Telegram($_ENV['TELEGRAM_BOT_TOKEN'], $_ENV['TELEGRAM_BOT_NAME']);
    Logger::info('Long polling запущен');
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    Logger::error("Telegram initialization failed: {$e->getMessage()}");
    exit();
}

// Список разрешенных типов обновлений
$allowedUpdates = [
    Update::TYPE_MESSAGE,
    Update::TYPE_EDITED_MESSAGE,
    Update::TYPE_CHANNEL_POST,
    Update::TYPE_EDITED_CHANNEL_POST,
    Update::TYPE_MESSAGE_REACTION,
    Update::TYPE_MESSAGE_REACTION_COUNT,
    Update::TYPE_INLINE_QUERY,
    Update::TYPE_CHOSEN_INLINE_RESULT,
    Update::TYPE_CALLBACK_QUERY,
    Update::TYPE_SHIPPING_QUERY,
    Update::TYPE_PRE_CHECKOUT_QUERY,
    Update::TYPE_POLL,
    Update::TYPE_POLL_ANSWER,
    Update::TYPE_MY_CHAT_MEMBER,
    Update::TYPE_CHAT_MEMBER,
    Update::TYPE_CHAT_JOIN_REQUEST,
    Update::TYPE_CHAT_BOOST,
    Update::TYPE_REMOVED_CHAT_BOOST,
];
// Получаем смещение для longpolling
$offset = getLongPollingOffset();

try {
    while (true) {
        try {
            // Получаем обновления
            $updates = Request::getUpdates([
                'offset' => $offset,
                'allowed_updates' => $allowedUpdates,
                'timeout' => 30,
                'limit' => 100,
            ])->getResult();
            
            // Если $updates не является массивом, делаем его пустым массивом
            if (!is_array($updates)) {
                $updates = [];
            }
            
            if (count($updates) === 0) {
                usleep(100000);
                continue;
            }
            
            /**
             * @var Update $update
             */
            foreach ($updates as $update) {
                // Получаем данные обновления
                $updateData = $update->getRawData();

                Logger::debug('Получено обновление', [
                    'id' => $update->getUpdateId(),
                    'type' => $update->getUpdateType(),
                ]);

                $offset = $update->getUpdateId() + 1;

                $redis->set(RedisHelper::REDIS_LONGPOLLING_OFFSET_KEY, $offset);

                try {
                    $redisFilter = RedisHelper::getInstance();
                } catch (\RedisException $e) {
                    $redisFilter = null;
                }
                $filter = new UpdateFilter(
                    $redisFilter,
                    $_ENV['TG_FILTERS_REDIS_PREFIX'] ?: 'tg:filters'
                );
                $reason = null;
                if (!$filter->shouldProcess($update, $reason)) {
                    Logger::info('Update skipped', [
                        'id' => $update->getUpdateId(),
                        'type' => $update->getUpdateType(),
                        'reason' => $reason,
                    ]);
                    continue;
                }

                $updateType = $update->getUpdateType();
                
                // Обрабатываем обновления
                $handled = match ($updateType) {
                    Update::TYPE_MESSAGE => $update->getMessage() !== null,
                    Update::TYPE_EDITED_MESSAGE => $update->getEditedMessage() !== null,
                    Update::TYPE_CHANNEL_POST => $update->getChannelPost() !== null,
                    Update::TYPE_EDITED_CHANNEL_POST => $update->getEditedChannelPost() !== null,
                    'message_reaction' => UpdateHelper::getMessageReaction($update) !== null,
                    'message_reaction_count' => UpdateHelper::getMessageReactionCount($update) !== null,
                    Update::TYPE_INLINE_QUERY => $update->getInlineQuery() !== null,
                    Update::TYPE_CHOSEN_INLINE_RESULT => $update->getChosenInlineResult() !== null,
                    Update::TYPE_CALLBACK_QUERY => $update->getCallbackQuery() !== null,
                    Update::TYPE_SHIPPING_QUERY => $update->getShippingQuery() !== null,
                    Update::TYPE_PRE_CHECKOUT_QUERY => $update->getPreCheckoutQuery() !== null,
                    Update::TYPE_POLL => $update->getPoll() !== null,
                    Update::TYPE_POLL_ANSWER => $update->getPollAnswer() !== null,
                    Update::TYPE_MY_CHAT_MEMBER => $update->getMyChatMember() !== null,
                    Update::TYPE_CHAT_MEMBER => $update->getChatMember() !== null,
                    Update::TYPE_CHAT_JOIN_REQUEST => $update->getChatJoinRequest() !== null,
                    'chat_boost' => UpdateHelper::getChatBoost($update) !== null,
                    'removed_chat_boost' => UpdateHelper::getRemovedChatBoost($update) !== null,
                    default => false,
                  };
                
                if ($handled) {
                    // Используем функцию для запуска обработки в отдельном процессе
                    $updateData = base64_encode(json_encode($update));
                    $command = 'php ' . dirname(__DIR__) . '/run worker:handler ' . escapeshellarg($updateData);
                    shell_exec($command);
                    Logger::debug('Обновление передано в обработчик', [
                        'id' => $update->getUpdateId(),
                        'type' => $updateType,
                    ]);
                } else {
                    Logger::error("Неизвестный тип обновления: {$updateType}");
                }
            }
        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
            Logger::error("Telegram getUpdates failed. {$e->getMessage()}");
        } catch (Exception $e) {
            Logger::error("Long polling error. {$e->getMessage()}");
        }
    }
} catch (Exception $e) {
    Logger::error("Fatal long polling error. {$e->getMessage()}");
    echo $e->getMessage();
}

function getLongPollingOffset(): int
{
    try {
        // Получаем инстанс RedisHelper (Redis)
        $redis = RedisHelper::getInstance();

        // Получаем offset из Redis
        $offset = $redis->get(RedisHelper::REDIS_LONGPOLLING_OFFSET_KEY);

        // Если offset не существует или равен false, то устанавливаем его в 0
        if ($offset === false) {
            $offset = 0;
        }
        
        // Преобразуем offset в целое число
        $offset = (int)$offset;
        
        // Если offset равен 0, то пытаемся получить его из базы данных
        if ($offset === 0) {
            $offset = getLongPollingOffsetFromDb();
        }
        
        // Возвращаем offset
        return $offset;
    } catch (\RedisException|\RuntimeException $e) {
        Logger::error("Failed to get long polling offset");
    }
    
    return 0;
}

function getLongPollingOffsetFromDb(): int
{
    $db = Database::getInstance(); // Получаем инстанс DatabaseHelper (PDO)
    
    $stmt = $db->query("SELECT `update_id` FROM `telegram_updates` ORDER BY `created_at` DESC LIMIT 1");
    $result = $stmt->fetchColumn();
    
    // Проверяем результат
    if ($result === false) {
        // Если нет записей в базе данных, то устанавливаем offset равным 0
        $result = 0;
    }
    
    // Преобразуем результат в число
    return (int)$result;
}
