<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * @since 5.3.0
 */
class Migration_20230516103300 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add upgrade release table';

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `releases` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `data` mediumtext NOT NULL,
          `returnCode` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`)
            VALUES ('UPGRADE', 'Upgrade')");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS releases');
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht` = 'UPGRADE'");
    }
}
