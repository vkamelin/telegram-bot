<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\EditedMessages;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractEditedMessageHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Метод для обработки отредактированного сообщения
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
