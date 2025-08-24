<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\EditedMessages;

use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultEditedMessageHandler extends AbstractEditedMessageHandler
{
    public function handle(Update $update): void
    {
        $message = $update->getEditedMessage();
        
        $raw = $message->getRawData();
        
        $entities = isset($raw['entities']);
    }
}
