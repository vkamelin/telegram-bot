<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\MessageReactionCounts;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractMessageReactionCountHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Метод для обработки обновления количества реакций на сообщение
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
