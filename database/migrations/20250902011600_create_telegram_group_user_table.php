<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTelegramGroupUserTable extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `telegram_group_user` (
            `group_id` INT UNSIGNED NOT NULL COMMENT 'Group identifier',
            `user_id` INT UNSIGNED NOT NULL COMMENT 'User identifier',
            PRIMARY KEY (`group_id`, `user_id`),
            CONSTRAINT `fk_tgu_group_id` FOREIGN KEY (`group_id`) REFERENCES `telegram_user_groups`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_tgu_user_id` FOREIGN KEY (`user_id`) REFERENCES `telegram_users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_unicode_ci;");
        $this->execute("CREATE INDEX `idx_telegram_group_user_group_id` ON `telegram_group_user` (`group_id`);");
        $this->execute("CREATE INDEX `idx_telegram_group_user_user_id` ON `telegram_group_user` (`user_id`);");
    }
}
