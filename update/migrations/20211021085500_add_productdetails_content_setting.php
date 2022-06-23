<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20211021085500
 */
class Migration_20211021085500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add productdetails content setting';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'artikeldetails_inhalt_anzeigen',
            'Y',
            CONF_ARTIKELDETAILS,
            'Inhalt anzeigen',
            'selectbox',
            1475,
            (object)[
                'cBeschreibung' => 'Inhalt in der Beschreibungs-Registerkarte anzeigen.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('artikeldetails_inhalt_anzeigen');
    }
}
