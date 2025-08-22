<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\InlineQueries;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractInlineQueryHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Метод для обработки InlineQuery
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
