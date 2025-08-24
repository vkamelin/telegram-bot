<?php

declare(strict_types=1);

namespace App\Telegram;

use Longman\TelegramBot\Entities\Update;

/**
 * Helper methods for extracting raw update data for unsupported update types.
 */
final class UpdateHelper
{
    private function __construct()
    {
    }

    /**
     * Get message reaction update data.
     */
    public static function getMessageReaction(Update $update): ?array
    {
        return $update->getRawData()['message_reaction'] ?? null;
    }

    /**
     * Get message reaction count update data.
     */
    public static function getMessageReactionCount(Update $update): ?array
    {
        return $update->getRawData()['message_reaction_count'] ?? null;
    }

    /**
     * Get chat boost update data.
     */
    public static function getChatBoost(Update $update): ?array
    {
        return $update->getRawData()['chat_boost'] ?? null;
    }

    /**
     * Get removed chat boost update data.
     */
    public static function getRemovedChatBoost(Update $update): ?array
    {
        return $update->getRawData()['removed_chat_boost'] ?? null;
    }
}

