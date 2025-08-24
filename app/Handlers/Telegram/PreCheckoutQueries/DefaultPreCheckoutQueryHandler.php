<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\PreCheckoutQueries;

use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultPreCheckoutQueryHandler extends AbstractPreCheckoutQueryHandler
{
    public function handle(Update $update): void
    {
        $preCheckoutQuery = $update->getPreCheckoutQuery();
        $orderInfo = $preCheckoutQuery->getOrderInfo();

        $this->answerPreCheckoutQuery($preCheckoutQuery->getId(), true);
    }
}
