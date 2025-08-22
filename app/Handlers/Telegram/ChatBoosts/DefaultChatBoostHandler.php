<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChatBoosts;

use App\Domain\ChatBoostsTable;
use App\Telegram\UpdateHelper;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChatBoostHandler extends AbstractChatBoostHandler
{
    public function handle(Update $update): void
    {
        $chatBoost = UpdateHelper::getChatBoost($update);
        if ($chatBoost === null) {
            return;
        }
        $chat = $chatBoost['chat'] ?? [];

        $repo = new ChatBoostsTable($this->db);
        $boost = $chatBoost['boost'] ?? null;

        try {
            $source = isset($boost['source'])
                ? json_encode($boost['source'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                : null;
        } catch (JsonException $e) {
            $source = null;
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
            'source' => $source,
            'start_at' => isset($boost['start_date']) ? date('c', $boost['start_date']) : null,
            'end_at' => isset($boost['expire_date']) ? date('c', $boost['expire_date']) : null,
            'payload' => $payload,
            'received_at' => date('c'),
        ]);
    }
}
