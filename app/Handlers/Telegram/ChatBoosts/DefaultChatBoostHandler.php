<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChatBoosts;

use App\Telegram\UpdateHelper;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChatBoostHandler extends AbstractChatBoostHandler
{
    public function handle(Update $update): void
    {
        $chatBoost = UpdateHelper::getChatBoost($update);
        if ($chatBoost === null) {
            return;
        }
        $chat = $chatBoost['chat'] ?? [];
        $boost = $chatBoost['boost'] ?? null;
    }
}
