<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20211130154000
 */
class Migration_20211130154000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add hide partlist setting';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'bestellvorgang_partlist',
            'Y',
            CONF_KAUFABWICKLUNG,
            'Stücklistenkomponenten anzeigen',
            'selectbox',
            505,
            (object)[
                'cBeschreibung' => 'Sollen die Stücklistenkomponenten in Warenkorb und Checkout angezeigt werden?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('bestellvorgang_partlist');
    }
}
