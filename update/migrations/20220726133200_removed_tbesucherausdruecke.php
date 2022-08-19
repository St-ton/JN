<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220726133200
 */
class Migration_20220726133200 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Removed table tbesuchersuchausdruecke';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tbesuchersuchausdruecke`');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `tbesuchersuchausdruecke` (
              `kBesucher` int(10) unsigned NOT NULL,
              `cSuchanfrage` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
              `cRohdaten` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
              KEY `cSuchanfrage` (`cSuchanfrage`),
              KEY `kBesucher` (`kBesucher`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }
}
