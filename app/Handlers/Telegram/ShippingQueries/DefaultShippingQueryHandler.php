<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ShippingQueries;

use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultShippingQueryHandler extends AbstractShippingQueryHandler
{
    public function handle(Update $update): void
    {
        $shippingQuery = $update->getShippingQuery();
        
        $address = $shippingQuery->getShippingAddress();

        $this->answerShippingQuery($shippingQuery->getId(), true, []);
    }
}
