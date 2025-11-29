<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSeedesTable extends AbstractMigration
{

    public function up(): void
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `seedes` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE = utf8mb4_unicode_ci;');
    }
}
