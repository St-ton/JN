<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200825095900
 */
class Migration_20200825095900 extends Migration implements IMigration
{
    protected $author      = 'je';
    protected $description = 'Add tuploadhistory table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tuploadhistory` (
          `kUploadHistory` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `cIP` varchar(255) NULL,
          `dErstellt` datetime NULL,
          PRIMARY KEY (`kUploadHistory`),
          KEY `cIP` (`cIP`)
          );"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tuploadhistory`');
    }
}
