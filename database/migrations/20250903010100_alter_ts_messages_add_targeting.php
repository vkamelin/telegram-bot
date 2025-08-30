<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AlterTsMessagesAddTargeting extends AbstractMigration
{
    public function up(): void
    {
        // Add targeting columns for batch scheduling and basic reporting counters
        $this->execute("ALTER TABLE `telegram_scheduled_messages`
            ADD COLUMN `target_type` ENUM('all','group','selected') NULL AFTER `status`,
            ADD COLUMN `target_group_id` INT UNSIGNED NULL AFTER `target_type`,
            ADD COLUMN `selected_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `target_group_id`,
            ADD COLUMN `success_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `selected_count`,
            ADD COLUMN `failed_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `success_count`
        ");

        // Helpful indexes
        $this->execute("CREATE INDEX IF NOT EXISTS `idx_ts_messages_target_type` ON `telegram_scheduled_messages` (`target_type`)");
        $this->execute("CREATE INDEX IF NOT EXISTS `idx_ts_messages_target_group_id` ON `telegram_scheduled_messages` (`target_group_id`)");
    }
}

