<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\InlineQueries;

use App\Domain\InlineQueriesTable;
use JsonException;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

class DefaultInlineQueryHandler extends AbstractInlineQueryHandler
{
    public function handle(Update $update): void
    {
        $inlineQuery = $update->getInlineQuery();

        $repo = new InlineQueriesTable($this->db);
        $location = $inlineQuery->getLocation();

        try {
            $locationJson = $location !== null
                ? json_encode($location, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                : null;
        } catch (JsonException $e) {
            $locationJson = null;
        }

        $repo->save([
            'inline_query_id' => $inlineQuery->getId(),
            'from_user_id' => $inlineQuery->getFrom()->getId(),
            'query' => $inlineQuery->getQuery(),
            'offset' => $inlineQuery->getOffset(),
            'location' => $locationJson,
            'language_code' => $inlineQuery->getFrom()->getLanguageCode(),
            'received_at' => date('c'),
        ]);

        Request::answerInlineQuery([
            'inline_query_id' => $inlineQuery->getId(),
            'results' => [],
            'cache_time' => 0,
            'is_personal' => true,
        ]);
    }
}
