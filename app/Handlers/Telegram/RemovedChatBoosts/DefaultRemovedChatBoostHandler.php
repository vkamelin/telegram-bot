<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\RemovedChatBoosts;

use App\Telegram\UpdateHelper;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultRemovedChatBoostHandler extends AbstractRemovedChatBoostHandler
{
    public function handle(Update $update): void
    {
        $removedChatBoost = UpdateHelper::getRemovedChatBoost($update);
        if ($removedChatBoost === null) {
            return;
        }
        
        $chat = $removedChatBoost['chat'] ?? [];
        $boost = $removedChatBoost['boost'] ?? null;
        $reason = $boost['reason'] ?? null;
        
    }
}
