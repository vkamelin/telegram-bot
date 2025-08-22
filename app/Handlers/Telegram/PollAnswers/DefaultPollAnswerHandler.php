<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\PollAnswers;

use App\Domain\PollAnswersTable;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultPollAnswerHandler extends AbstractPollAnswerHandler
{
    public function handle(Update $update): void
    {
        $pollAnswer = $update->getPollAnswer();

        $repo = new PollAnswersTable($this->db);
        $optionIds = $pollAnswer->getOptionIds();
        try {
            $optionJson = json_encode($optionIds, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            $optionJson = '[]';
        }

        $repo->save([
            'poll_id' => $pollAnswer->getPollId(),
            'user_id' => $pollAnswer->getUser()->getId(),
            'option_ids' => $optionJson,
            'answered_at' => date('c'),
        ]);
    }
}
