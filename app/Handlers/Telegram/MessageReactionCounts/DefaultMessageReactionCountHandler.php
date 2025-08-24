<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\MessageReactionCounts;

use App\Telegram\UpdateHelper;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultMessageReactionCountHandler extends AbstractMessageReactionCountHandler
{
    public function handle(Update $update): void
    {
        $reaction = UpdateHelper::getMessageReactionCount($update);
        if ($reaction === null) {
            return;
        }
        
        $reactions = $reaction['reactions'] ?? [];
    }
}
