<?php
/**
 * @author ms
 * @created Wed, 03 Apr 2019 16:18:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190403161800
 */
class Migration_20190403161800 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'removes vcard';

    public function up()
    {
        $this->removeConfig('kundenregistrierung_vcardupload');
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'uploadVCard'");
    }

    public function down()
    {
        $this->setConfig(
            'kundenregistrierung_vcardupload',
            'Y',
            \CONF_KUNDEN,
            'vCard Upload erlauben',
            'selectbox',
            240,
            (object)[
                'cBeschreibung' => 'Erlaubt dem Kunden bei der Registrierung das Hochladen einer elektronischen ' .
                    'Visitenkarte (vCard) im vcf-Format.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ]
            ]
        );

        $this->setLocalization('ger', 'account data', 'uploadVCard', 'vCard hochladen');
        $this->setLocalization('eng', 'account data', 'uploadVCard', 'Upload vCard');
    }
}
