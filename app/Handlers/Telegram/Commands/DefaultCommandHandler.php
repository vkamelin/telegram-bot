<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\Commands;

use Longman\TelegramBot\Entities\Update;

class DefaultCommandHandler extends AbstractCommandHandler
{
    /**
     * @param Update $update
     * @return void
     */
    public function handle(Update $update): void
    {

    }
}
