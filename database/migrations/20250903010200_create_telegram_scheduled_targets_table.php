<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTelegramScheduledTargetsTable extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `telegram_scheduled_targets` (
            `scheduled_id` BIGINT UNSIGNED NOT NULL,
            `target_user_id` BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (`scheduled_id`, `target_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_unicode_ci;");

        $this->execute("CREATE INDEX IF NOT EXISTS `idx_tst_scheduled_id` ON `telegram_scheduled_targets` (`scheduled_id`)");
        $this->execute("CREATE INDEX IF NOT EXISTS `idx_tst_target_user_id` ON `telegram_scheduled_targets` (`target_user_id`)");
    }
}

