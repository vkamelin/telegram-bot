<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\PreCheckoutQueries;

use App\Domain\TgPreCheckoutTable;
use JsonException;
use Longman\TelegramBot\Entities\Update;

class DefaultPreCheckoutQueryHandler extends AbstractPreCheckoutQueryHandler
{
    public function handle(Update $update): void
    {
        $preCheckoutQuery = $update->getPreCheckoutQuery();

        $repo = new TgPreCheckoutTable($this->db);
        $orderInfo = $preCheckoutQuery->getOrderInfo();
        try {
            $orderJson = $orderInfo !== null ? json_encode($orderInfo, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) : null;
        } catch (JsonException $e) {
            $orderJson = null;
        }

        $repo->save([
            'pre_checkout_query_id' => $preCheckoutQuery->getId(),
            'from_user_id' => $preCheckoutQuery->getFrom()->getId(),
            'currency' => $preCheckoutQuery->getCurrency(),
            'total_amount' => $preCheckoutQuery->getTotalAmount(),
            'invoice_payload' => $preCheckoutQuery->getInvoicePayload(),
            'shipping_option_id' => $preCheckoutQuery->getShippingOptionId(),
            'order_info' => $orderJson,
            'received_at' => date('c'),
        ]);

        $this->answerPreCheckoutQuery($preCheckoutQuery->getId(), true);
    }
}
