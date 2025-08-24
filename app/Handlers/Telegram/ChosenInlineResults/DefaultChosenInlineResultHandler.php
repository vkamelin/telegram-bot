<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChosenInlineResults;

use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultChosenInlineResultHandler extends AbstractChosenInlineResultHandler
{
    public function handle(Update $update): void
    {
        $chosen = $update->getChosenInlineResult();
        $location = $chosen->getLocation();
    }
}
