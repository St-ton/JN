<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220120094400
 */
class Migration_20220120094400 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add min value bestseller config';


    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'startseite_bestseller_minprice',
            '0',
            CONF_STARTSEITE,
            'Bestseller: Minimaler Nettopreis',
            'number',
            190,
            (object)[
                'cBeschreibung' => 'Mindest Nettopreis damit Artikel als Bestseller angezeigt werden. Bezieht sich auf den Netto-Vk aus JTL-Wawi (in Datenbank: tartikel.fStandardpreisNetto).',
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
        $this->removeConfig('startseite_bestseller_minprice');
    }
}
