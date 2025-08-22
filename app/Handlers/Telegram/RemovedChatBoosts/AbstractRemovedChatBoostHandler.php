<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\RemovedChatBoosts;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractRemovedChatBoostHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Method for handling removed_chat_boost update
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
