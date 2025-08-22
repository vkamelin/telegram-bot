<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChannelPosts;

use App\Domain\ChannelPostsTable;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChannelPostHandler extends AbstractChannelPostHandler
{
    public function handle(Update $update): void
    {
        $message = $update->getChannelPost() ?? $update->getEditedChannelPost();

        $repo = new ChannelPostsTable($this->db);
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

        try {
            $forward = isset($raw['forward_from_chat'])
                ? json_encode($raw['forward_from_chat'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                : null;
        } catch (JsonException $e) {
            $forward = null;
        }

        try {
            $linkPreview = isset($raw['link_preview_options'])
                ? json_encode($raw['link_preview_options'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                : null;
        } catch (JsonException $e) {
            $linkPreview = null;
        }

        $repo->save([
            'chat_id' => $message->getChat()->getId(),
            'message_id' => $message->getMessageId(),
            'date' => date('c', $message->getDate()),
            'author_signature' => $message->getAuthorSignature(),
            'text' => $message->getText(),
            'caption' => $message->getCaption(),
            'entities' => $entities,
            'media' => $media,
            'is_automatic_forward' => (bool) ($raw['is_automatic_forward'] ?? false),
            'forward_from_chat' => $forward,
            'link_preview_options' => $linkPreview,
        ]);
    }
}
