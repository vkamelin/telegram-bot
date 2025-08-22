<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChosenInlineResults;

use App\Domain\InlineChosenResultsTable;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChosenInlineResultHandler extends AbstractChosenInlineResultHandler
{
    public function handle(Update $update): void
    {
        $chosen = $update->getChosenInlineResult();

        $repo = new InlineChosenResultsTable($this->db);
        $location = $chosen->getLocation();

        try {
            $locationJson = $location !== null
                ? json_encode($location, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
                : null;
        } catch (JsonException $e) {
            $locationJson = null;
        }

        $repo->save([
            'result_id' => $chosen->getResultId(),
            'from_user_id' => $chosen->getFrom()->getId(),
            'query' => $chosen->getQuery(),
            'inline_message_id' => $chosen->getInlineMessageId(),
            'location' => $locationJson,
            'chosen_at' => date('c'),
        ]);
    }
}
