<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\Messages;

use App\Services\FlowState;
use App\Services\TelegramState;
use App\Domain\TelegramSessionTable;
use App\Services\RedisService;
use App\Services\Push;
use App\Services\PromptLoader;
use App\Logger;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Update;
use PDO;
use Throwable;
use RuntimeException;

/**
 * Обработчик текстового сообщения
 *
 * @property PDO $db
 */
class TextMessageHandler extends AbstractMessageHandler
{
    public function handle(Update $update): void
    {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $messageText = trim($message->getText() ?? '');

        $sessionRepo = new TelegramSessionTable($this->db);
        $session = $sessionRepo->findByUserId($chatId);

        FlowState::update((string) $chatId, ['last_message' => $messageText]);
    }
}
