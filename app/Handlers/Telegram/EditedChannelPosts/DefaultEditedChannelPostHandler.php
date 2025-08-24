<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\EditedChannelPosts;

use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultEditedChannelPostHandler extends AbstractEditedChannelPostHandler
{
    public function handle(Update $update): void
    {
        $message = $update->getEditedChannelPost();
        
        $raw = $message->getRawData();
        
        $entities = isset($raw['entities']);
    }
}
