<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\PollAnswers;

use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultPollAnswerHandler extends AbstractPollAnswerHandler
{
    public function handle(Update $update): void
    {
        $pollAnswer = $update->getPollAnswer();
        $optionIds = $pollAnswer->getOptionIds();
    }
}
