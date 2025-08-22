<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\ChosenInlineResults;

use App\Services\Db;
use Longman\TelegramBot\Entities\Update;
use PDO;

abstract class AbstractChosenInlineResultHandler
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Db::get();
    }

    /**
     * Handle chosen inline result updates.
     *
     * @param Update $update
     */
    abstract public function handle(Update $update): void;
}
