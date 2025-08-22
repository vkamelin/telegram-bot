<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChatMembers;

use App\Domain\ChatMemberUpdatesTable;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChatMemberHandler extends AbstractChatMemberHandler
{
    public function handle(Update $update): void
    {
        $chatMember = $update->getChatMember();

        $repo = new ChatMemberUpdatesTable($this->db);
        $newMember = $chatMember->getNewChatMember();
        $oldMember = $chatMember->getOldChatMember();

        try {
            $rights = json_encode($newMember->getRawData(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            $rights = null;
        }

        $repo->save([
            'chat_id' => $chatMember->getChat()->getId(),
            'target_user_id' => $newMember->getUser()->getId(),
            'actor_user_id' => $chatMember->getFrom()->getId(),
            'old_status' => $oldMember?->getStatus(),
            'new_status' => $newMember->getStatus(),
            'rights' => $rights,
            'until_date' => $newMember->getUntilDate() ? date('c', $newMember->getUntilDate()) : null,
            'changed_at' => date('c'),
        ]);
    }
}
