<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChatMembers;

use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChatMemberHandler extends AbstractChatMemberHandler
{
    public function handle(Update $update): void
    {
        $chatMember = $update->getChatMember();
        
        $newMember = $chatMember->getNewChatMember();
        $oldMember = $chatMember->getOldChatMember();
    }
}
