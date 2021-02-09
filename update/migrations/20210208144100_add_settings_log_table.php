<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210208144100
 */
class Migration_20210208144100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add settings log table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            'CREATE TABLE IF NOT EXISTS `teinstellungenlog` (
                `kEinstellungenLog`     INT          NOT NULL AUTO_INCREMENT,
                `kAdminlogin`           INT          NOT NULL,
                `cEinstellungenName`    VARCHAR(255) NOT NULL,
                `cEinstellungenWertAlt` VARCHAR(255) NOT NULL,
                `cEinstellungenWertNeu` VARCHAR(255) NOT NULL,
                `dDatum`                DATETIME     NOT NULL,
                PRIMARY KEY (`kEinstellungenLog`),
                KEY (`cEinstellungenName`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        );
        $this->execute('ALTER TABLE teinstellungenconf ADD COLUMN cWertDefault VARCHAR(255) NOT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `teinstellungenlog`');
        $this->execute('ALTER TABLE teinstellungenconf DROP COLUMN cWertDefault');
    }
}
