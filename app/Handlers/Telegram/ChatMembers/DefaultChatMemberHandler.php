<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChatMembers;

use Longman\TelegramBot\Entities\Update;

class DefaultChatMemberHandler extends AbstractChatMemberHandler
{
    public function handle(Update $update): void
    {
        $chatMember = $update->getChatMember();
        if ($chatMember === null) {
            return;
        }

        $chatId = $chatMember->getChat()->getId();
        $newMember = $chatMember->getNewChatMember();
        $userId = $newMember->getUser()->getId();
        $status = $newMember->getStatus();

        $state = $status === 'kicked' ? 'declined' : 'approved';

        $stmt = $this->db->prepare(
            'INSERT INTO chat_members (chat_id, user_id, role, state) ' .
            'VALUES (:chat_id, :user_id, :role, :state) ' .
            'ON DUPLICATE KEY UPDATE role = VALUES(role), state = VALUES(state)'
        );
        $stmt->execute([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'role' => $status,
            'state' => $state,
        ]);
    }
}
