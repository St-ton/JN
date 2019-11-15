<?php
/**
 * Remove mosaic setting
 *
 * @author mh
 * @created Fri, 15 Nov 2019 15:41:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191115154100
 */
class Migration_20191115154100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove mosaic setting';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->setConfig(
            'products_per_page_gallery',
            '10,20,30,40,50',
            \CONF_ARTIKELUEBERSICHT,
            'Auswahloptionen Artikel pro Seite in Gallerieansicht',
            'text',
            855,
            (object)[
                'cBeschreibung' => 'Mit Komma getrennt, -1 für alle',
            ],
            true
        );
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->setConfig(
            'products_per_page_gallery',
            '9,12,15,18,21',
            \CONF_ARTIKELUEBERSICHT,
            'Auswahloptionen Artikel pro Seite in Gallerieansicht',
            'text',
            855,
            (object)[
                'cBeschreibung' => 'Mit Komma getrennt, -1 für alle',
            ],
            true
        );
    }
}
