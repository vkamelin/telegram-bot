<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChatJoinRequests;

use App\Domain\ChatJoinRequestsTable;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChatJoinRequestHandler extends AbstractChatJoinRequestHandler
{
    public function handle(Update $update): void
    {
        $chatJoinRequest = $update->getChatJoinRequest();

        $repo = new ChatJoinRequestsTable($this->db);
        $invite = $chatJoinRequest->getInviteLink();
        try {
            $inviteJson = $invite !== null ? json_encode($invite, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) : null;
        } catch (JsonException $e) {
            $inviteJson = null;
        }

        $repo->save([
            'chat_id' => $chatJoinRequest->getChat()->getId(),
            'user_id' => $chatJoinRequest->getFrom()->getId(),
            'bio' => $chatJoinRequest->getBio(),
            'invite_link' => $inviteJson,
            'requested_at' => date('c', $chatJoinRequest->getDate()),
            'status' => 'pending',
        ]);
    }
}
