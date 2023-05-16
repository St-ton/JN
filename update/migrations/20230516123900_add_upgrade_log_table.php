<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * @since 5.3.0
 */
class Migration_20230516123900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add upgrade log table';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `upgrade_log` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `version_from` varchar(255) NOT NULL,
          `version_to` varchar(255) NOT NULL,
          `backup_db` mediumtext DEFAULT NULL,
          `backup_fs` mediumtext DEFAULT NULL,
          `errors` mediumtext DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS upgrade_log');
    }
}
