<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\MessageReactions;

use App\Domain\MessageReactionsUserTable;
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

        $repo = new MessageReactionsUserTable($this->db);
        $newReaction = $reaction['new_reaction'] ?? [];

        try {
            $reactionData = json_encode($newReaction, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            $reactionData = null;
        }

        $repo->save([
            'chat_id' => $reaction['chat']['id'] ?? 0,
            'message_id' => $reaction['message_id'] ?? 0,
            'user_id' => $reaction['user']['id'] ?? 0,
            'reactions' => $reactionData ?? '[]',
            'updated_at' => date('c', $reaction['date'] ?? time()),
        ]);
    }
}
