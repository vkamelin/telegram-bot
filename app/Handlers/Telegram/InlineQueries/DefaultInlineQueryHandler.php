<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\InlineQueries;

use JsonException;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

class DefaultInlineQueryHandler extends AbstractInlineQueryHandler
{
    public function handle(Update $update): void
    {
        $inlineQuery = $update->getInlineQuery();
        
        $location = $inlineQuery->getLocation();

        Request::answerInlineQuery([
            'inline_query_id' => $inlineQuery->getId(),
            'results' => [],
            'cache_time' => 0,
            'is_personal' => true,
        ]);
    }
}
