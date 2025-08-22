<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\Polls;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractPollHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Handle incoming poll update.
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
