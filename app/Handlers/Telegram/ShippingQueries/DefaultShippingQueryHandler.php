<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ShippingQueries;

use App\Domain\TgShippingQueriesTable;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultShippingQueryHandler extends AbstractShippingQueryHandler
{
    public function handle(Update $update): void
    {
        $shippingQuery = $update->getShippingQuery();

        $repo = new TgShippingQueriesTable($this->db);
        $address = $shippingQuery->getShippingAddress();
        try {
            $addressJson = json_encode($address, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            $addressJson = null;
        }

        $repo->save([
            'shipping_query_id' => $shippingQuery->getId(),
            'from_user_id' => $shippingQuery->getFrom()->getId(),
            'invoice_payload' => $shippingQuery->getInvoicePayload(),
            'shipping_address' => $addressJson ?? '{}',
            'received_at' => date('c'),
        ]);

        $this->answerShippingQuery($shippingQuery->getId(), true, []);
    }
}
