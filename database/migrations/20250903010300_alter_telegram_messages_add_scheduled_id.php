<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AlterTelegramMessagesAddScheduledId extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `telegram_messages`
            ADD COLUMN `scheduled_id` BIGINT UNSIGNED NULL AFTER `type`
        ");
        $this->execute("CREATE INDEX IF NOT EXISTS `idx_tm_scheduled_id` ON `telegram_messages` (`scheduled_id`)");
    }
}

