<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\Commands;

use App\Helpers\Push;
use App\Helpers\MessageStorage;
use Longman\TelegramBot\Entities\Update;
use Exception;

class AboutCommandHandler extends AbstractCommandHandler
{
    /**
     * Handle /about command
     *
     * @param Update $update
     * @throws Exception
     */
    public function handle(Update $update): void
    {
        $chatId = $update->getMessage()->getChat()->getId();
        $text = MessageStorage::read('about') ?? 'Информация о проекте временно недоступна.';
        Push::text($chatId, $text, 'about', 1);
    }
}
