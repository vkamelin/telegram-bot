<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\EditedMessages;

use App\Domain\MessageEditsTable;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultEditedMessageHandler extends AbstractEditedMessageHandler
{
    public function handle(Update $update): void
    {
        $message = $update->getEditedMessage();

        $repo = new MessageEditsTable($this->db);
        $raw = $message->getRawData();

        try {
            $entities = isset($raw['entities'])
                ? json_encode($raw['entities'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                : null;
        } catch (JsonException $e) {
            $entities = null;
        }

        try {
            $media = json_encode($raw, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            $media = null;
        }

        $repo->save([
            'chat_id' => $message->getChat()->getId(),
            'message_id' => $message->getMessageId(),
            'editor_user_id' => $message->getFrom()->getId(),
            'edit_date' => date('c', $message->getEditDate()),
            'new_text' => $message->getText(),
            'new_caption' => $message->getCaption(),
            'entities' => $entities,
            'media' => $media,
            'is_channel' => false,
        ]);
    }
}
