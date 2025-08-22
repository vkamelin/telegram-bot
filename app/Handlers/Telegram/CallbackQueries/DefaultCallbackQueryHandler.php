<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\CallbackQueries;

use Longman\TelegramBot\Entities\Update;

class DefaultCallbackQueryHandler extends AbstractCallbackQueryHandler
{
    public function handle(Update $update): void
    {
        $userId = $update->getCallbackQuery()->getFrom()->getId();
        $callbackQueryId = (int) $update->getCallbackQuery()->getId();

        // Simply acknowledge the callback query to avoid "loading" animation
        $this->answerCallbackQuery($userId, $callbackQueryId);
    }
}
