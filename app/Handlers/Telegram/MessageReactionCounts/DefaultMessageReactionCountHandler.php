<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\MessageReactionCounts;

use App\Domain\MessageReactionsAggTable;
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

        $repo = new MessageReactionsAggTable($this->db);
        $reactions = $reaction['reactions'] ?? [];

        try {
            $reactionData = json_encode($reactions, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            $reactionData = '[]';
        }

        $repo->save([
            'chat_id' => $reaction['chat']['id'] ?? 0,
            'message_id' => $reaction['message_id'] ?? 0,
            'agg' => $reactionData,
            'updated_at' => date('c', $reaction['date'] ?? time()),
        ]);
    }
}
