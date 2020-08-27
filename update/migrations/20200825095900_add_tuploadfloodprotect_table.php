<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200825095900
 */
class Migration_20200825095900 extends Migration implements IMigration
{
    protected $author      = 'je';
    protected $description = 'Add tuploadfloodprotect table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tuploadfloodprotect` (
          `kUploadFloodProtect` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `cIP` varchar(255) NULL,
          `dErstellt` datetime NULL,
          PRIMARY KEY (`kUploadFloodProtect`),
          KEY `cIP` (`cIP`)
          );"
        );
        $this->setConfig(
            'upload_modul_limit',
            '10',
            \CONF_ARTIKELDETAILS,
            'Limit für Uploads pro Stunde',
            'number',
            499,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, wie oft ein Benutzer bei aktiviertem Uploadmodul Dateien hochladen darf (pro Stunde).'
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tuploadfloodprotect`');
        $this->removeConfig('upload_modul_limit');
    }
}
