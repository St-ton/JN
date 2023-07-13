<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230620131000
 */
class Migration_20230620131000 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Add admin sessions table';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute(
            'CREATE TABLE IF NOT EXISTS `active_admin_sessions` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
              `valid` TINYINT(1) NOT NULL DEFAULT 1,
              `sessionID` VARCHAR(255) NOT NULL,
              `userID` INT NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS `active_admin_sessions`');
    }
}
