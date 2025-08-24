<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChannelPosts;

use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChannelPostHandler extends AbstractChannelPostHandler
{
    public function handle(Update $update): void
    {
        $message = $update->getChannelPost() ?? $update->getEditedChannelPost();
        
        $raw = $message->getRawData();
        $entities = isset($raw['entities']);
        $forward = isset($raw['forward_from_chat']);
        $linkPreview = isset($raw['link_preview_options']);
    }
}
