<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\MessageReactions;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractMessageReactionHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Метод для обработки реакции на сообщение
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
