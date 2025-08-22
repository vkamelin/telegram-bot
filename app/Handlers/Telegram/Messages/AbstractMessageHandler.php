<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\Messages;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractMessageHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Метод для обработки сообщения
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
