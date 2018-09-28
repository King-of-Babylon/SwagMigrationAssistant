<?php declare(strict_types=1);

namespace SwagMigrationNext\Core\Version;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1538041441UpdateMapping extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1538041441;
    }

    public function update(Connection $connection): void
    {
        $this->addProfileIdColumn($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
        $this->dropProfileColumn($connection);
    }

    private function addProfileIdColumn(Connection $connection): void
    {
        // add profileId column
        $sql = <<<SQL
ALTER TABLE `swag_migration_mapping` ADD `profile_id` BINARY(16) NULL DEFAULT NULL AFTER `profile`,
ADD `profile_tenant_id` BINARY(16) NULL DEFAULT NULL AFTER `profile_id`;
SQL;
        $connection->exec($sql);

        // change profile column to nullable
        $sql = <<<SQL
ALTER TABLE `swag_migration_mapping` MODIFY `profile` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
SQL;
        $connection->exec($sql);

        // Set foreign key
        $sql = <<<SQL
ALTER TABLE `swag_migration_mapping` ADD FOREIGN KEY (`profile_id`) REFERENCES `swag_migration_profile`(`id`) ON DELETE SET NULL ON UPDATE NO ACTION;
SQL;
        $connection->exec($sql);

        // Get profile id from profile table
        $sql = <<<SQL
SELECT id FROM `swag_migration_profile` WHERE `profile`='shopware55' AND `gateway`='api';
SQL;
        $profileId = $connection->fetchColumn($sql);
        if ($profileId === false) {
            return;
        }

        // Update profile in run table
        $sql = <<<SQL
UPDATE `swag_migration_mapping` SET `profile_id`=:profileId WHERE `profile_name`='shopware55';
SQL;
        $connection->executeUpdate($sql, [
            'profileId' => $profileId,
        ]);
    }

    private function dropProfileColumn(Connection $connection): void
    {
        $sql = <<<SQL
ALTER TABLE `swag_migration_mapping` DROP `profile`;
SQL;
        $connection->exec($sql);
    }
}
