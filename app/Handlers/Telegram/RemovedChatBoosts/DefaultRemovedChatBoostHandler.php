<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\RemovedChatBoosts;

use App\Domain\ChatBoostsRemovedTable;
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

        $repo = new ChatBoostsRemovedTable($this->db);
        $boost = $removedChatBoost['boost'] ?? null;

        try {
            $reason = isset($boost['reason'])
                ? json_encode($boost['reason'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                : null;
        } catch (JsonException $e) {
            $reason = null;
        }

        try {
            $payload = $boost !== null
                ? json_encode($boost, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                : null;
        } catch (JsonException $e) {
            $payload = null;
        }

        $repo->save([
            'id' => $boost['boost_id'] ?? bin2hex(random_bytes(8)),
            'chat_id' => $chat['id'] ?? 0,
            'reason' => $reason,
            'removed_at' => date('c'),
            'payload' => $payload,
        ]);
    }
}
