<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChatJoinRequests;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractChatJoinRequestHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Метод для обработки обновления chat_join_request
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
