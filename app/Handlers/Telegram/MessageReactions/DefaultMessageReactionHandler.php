<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\MessageReactions;

use App\Telegram\UpdateHelper;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultMessageReactionHandler extends AbstractMessageReactionHandler
{
    public function handle(Update $update): void
    {
        $reaction = UpdateHelper::getMessageReaction($update);
        if ($reaction === null) {
            return;
        }
        
        $newReaction = $reaction['new_reaction'] ?? [];
    }
}
