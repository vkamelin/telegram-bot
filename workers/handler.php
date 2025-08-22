<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Logger;
use App\Services\Db;
use App\Services\RedisService;
use App\Handlers\Telegram\CallbackQueryHandler;
use App\Handlers\Telegram\MessageHandler;
use App\Handlers\Telegram\EditedMessageHandler;
use App\Handlers\Telegram\ChannelPostHandler;
use App\Handlers\Telegram\EditedChannelPostHandler;
use App\Handlers\Telegram\InlineQueryHandler;
use App\Handlers\Telegram\MessageReactionHandler;
use App\Handlers\Telegram\MessageReactionCountHandler;
use App\Handlers\Telegram\ChosenInlineResultHandler;
use App\Handlers\Telegram\ShippingQueryHandler;
use App\Handlers\Telegram\PreCheckoutQueryHandler;
use App\Handlers\Telegram\PollHandler;
use App\Handlers\Telegram\PollAnswerHandler;
use App\Handlers\Telegram\MyChatMemberHandler;
use App\Handlers\Telegram\ChatMemberHandler;
use App\Handlers\Telegram\ChatJoinRequestHandler;
use App\Handlers\Telegram\ChatBoostHandler;
use App\Handlers\Telegram\RemovedChatBoostHandler;
use App\Telegram\UpdateHelper as TelegramUpdateHelper;
use App\Support\RedisKeyHelper;
use App\Support\UpdateHelper;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;


try {
    if ($_ENV['TELEGRAM_API_SERVER'] === 'local') {
        $apiBaseUri = 'http://' . $_ENV['TELEGRAM_LOCAL_API_HOST'] . ':' . $_ENV['TELEGRAM_LOCAL_API_PORT'];
        $apiBaseDownloadUri = '/root/telegram-bot-api/' . $_ENV['TELEGRAM_BOT_TOKEN'];
        Request::setCustomBotApiUri($apiBaseUri, $apiBaseDownloadUri);
    }
    
    $telegram = new Telegram($_ENV['TELEGRAM_BOT_TOKEN'], $_ENV['TELEGRAM_BOT_NAME']);
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    file_put_contents('error.log', "Telegram initialization failed: {$e->getMessage()}\n", FILE_APPEND);
    Logger::error("Telegram initialization failed: {$e->getMessage()}");
    exit();
}

try {
    // Получаем данные из аргументов командной строки
    $updateData = json_decode(base64_decode($argv[1]), true, 512, JSON_THROW_ON_ERROR);
    
    // Передаем данные обновления в экземпляр класса Longman\TelegramBot\Entities\Update
    $update = new Update($updateData, $_ENV['TELEGRAM_BOT_NAME']);
    // Получаем тип обновления
    $updateType = $update->getUpdateType();
    $messageReaction = TelegramUpdateHelper::getMessageReaction($update);
    $messageReactionCount = TelegramUpdateHelper::getMessageReactionCount($update);

    try {
        $redis = RedisService::get();
        $dedupKey = RedisKeyHelper::key('telegram', 'update', (string)$update->getUpdateId());
        $stored = $redis->set($dedupKey, 1, ['nx', 'ex' => 60]);
        if ($stored === false) {
            Logger::info('Duplicate update skipped', ['id' => $update->getUpdateId()]);
            exit();
        }
    } catch (RuntimeException $e) {
        Logger::error('Redis initialization failed: ' . $e->getMessage());
    }
    
    if (empty($updateData)) {
        throw new RuntimeException("Не удалось декодировать данные.");
    }
    
    $db = Db::get();
    
    $userId = UpdateHelper::getUserId($update);
    if ($userId === null) {
        if ($updateType === Update::TYPE_POLL || in_array($updateType, ['message_reaction_count', 'chat_boost', 'removed_chat_boost'], true)) {
            $userId = 0;
        } else {
            throw new RuntimeException('User ID not found in update');
        }
    }

    $messageId = match ($updateType) {
        Update::TYPE_MESSAGE => $update->getMessage()->getMessageId(),
        Update::TYPE_EDITED_MESSAGE => $update->getEditedMessage()->getMessageId(),
        Update::TYPE_CHANNEL_POST => $update->getChannelPost()->getMessageId(),
        Update::TYPE_EDITED_CHANNEL_POST => $update->getEditedChannelPost()->getMessageId(),
        'message_reaction' => $messageReaction['message_id'] ?? null,
        'message_reaction_count' => $messageReactionCount['message_id'] ?? null,
        default => null,
    };

    $date = match ($updateType) {
        Update::TYPE_MESSAGE => $update->getMessage()->getDate(),
        Update::TYPE_EDITED_MESSAGE => $update->getEditedMessage()->getEditDate() ?? time(),
        Update::TYPE_CHANNEL_POST => $update->getChannelPost()->getDate(),
        Update::TYPE_EDITED_CHANNEL_POST => $update->getEditedChannelPost()->getEditDate() ?? time(),
        'message_reaction' => $messageReaction['date'] ?? time(),
        'message_reaction_count' => $messageReactionCount['date'] ?? time(),
        Update::TYPE_MY_CHAT_MEMBER => $update->getMyChatMember()->getDate(),
        Update::TYPE_CHAT_MEMBER => $update->getChatMember()->getDate(),
        Update::TYPE_CHAT_JOIN_REQUEST => $update->getChatJoinRequest()->getDate(),
        default => time(),
    };
    
    $stmt = $db->prepare(
        "INSERT INTO `telegram_updates` (`update_id`, `user_id`, `message_id`, `type`, `data`, `sent_at`) VALUES (:update_id, :user_id, :message_id, :type, :data, :sent_at)"
    );
    $result = $stmt->execute([
        'update_id' => $update->getUpdateId(),
        'user_id' => $userId,
        'message_id' => $messageId,
        'type' => $updateType,
        'data' => json_encode($update->getRawData(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        'sent_at' => date('Y-m-d H:i:s', $date)
    ]);
    
    if (!$result) {
        echo "Не удалось сохранить данные обновления в базу данных: {$stmt->errorInfo()[2]}";
        file_put_contents('error.log', "Не удалось сохранить данные обновления в базу данных: {$stmt->errorInfo()[2]}\n", FILE_APPEND);
        Logger::error("Не удалось сохранить данные обновления в базу данных: {$stmt->errorInfo()[2]}");
    }
    
    // Определяем обработчик в зависимости от типа обновления
    $updateType = $update->getUpdateType();
    $handler = match ($updateType) {
        Update::TYPE_MESSAGE => new MessageHandler(),
        Update::TYPE_EDITED_MESSAGE => new EditedMessageHandler(),
        Update::TYPE_CHANNEL_POST => new ChannelPostHandler(),
        Update::TYPE_EDITED_CHANNEL_POST => new EditedChannelPostHandler(),
        Update::TYPE_INLINE_QUERY => new InlineQueryHandler(),
        Update::TYPE_CHOSEN_INLINE_RESULT => new ChosenInlineResultHandler(),
        Update::TYPE_CALLBACK_QUERY => new CallbackQueryHandler(),
        Update::TYPE_SHIPPING_QUERY => new ShippingQueryHandler(),
        Update::TYPE_PRE_CHECKOUT_QUERY => new PreCheckoutQueryHandler(),
        'message_reaction' => new MessageReactionHandler(),
        'message_reaction_count' => new MessageReactionCountHandler(),
        Update::TYPE_POLL => new PollHandler(),
        Update::TYPE_POLL_ANSWER => new PollAnswerHandler(),
        Update::TYPE_MY_CHAT_MEMBER => new MyChatMemberHandler(),
        Update::TYPE_CHAT_MEMBER => new ChatMemberHandler(),
        Update::TYPE_CHAT_JOIN_REQUEST => new ChatJoinRequestHandler(),
        'chat_boost' => new ChatBoostHandler(),
        'removed_chat_boost' => new RemovedChatBoostHandler(),
    };

    // Обрабатываем обновление
    $handler->handle($update);
} catch (\Throwable $e) {
    echo "Ошибка при обработке обновления: {$e->getMessage()}";
    file_put_contents('error.log', "Ошибка при обработке обновления: {$e->getMessage()}\n", FILE_APPEND);
    Logger::error("Ошибка при обработке обновления: {$e->getMessage()}\n" . $e->getTraceAsString());
}
