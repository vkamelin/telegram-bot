<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChatMembers;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractChatMemberHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Метод для обработки обновления chat_member
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
