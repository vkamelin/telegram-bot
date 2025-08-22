<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\EditedChannelPosts;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractEditedChannelPostHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Метод для обработки отредактированного сообщения канала
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
