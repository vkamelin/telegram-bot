<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChatJoinRequests;

use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChatJoinRequestHandler extends AbstractChatJoinRequestHandler
{
    public function handle(Update $update): void
    {
        $chatJoinRequest = $update->getChatJoinRequest();
        $invite = $chatJoinRequest->getInviteLink();
    }
}
