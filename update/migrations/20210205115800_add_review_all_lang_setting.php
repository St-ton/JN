<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210205115800
 */
class Migration_20210205115800 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add review all lang setting';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'bewertung_alle_sprachen',
            'Y',
            CONF_BEWERTUNG,
            'Bewertungen aller Sprachen auf Artikeldetailseite anzeigen.',
            'selectbox',
            130,
            (object)[
                'cBeschreibung' => 'Bewertungen aller Sprachen auf Artikeldetailseite anzeigen.',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );

        $this->setLocalization('ger', 'product rating', 'reviewsInAllLang', 'Bewertungen in allen Sprachen:');
        $this->setLocalization('eng', 'product rating', 'reviewsInAllLang', 'Reviews in all languages:');
        $this->setLocalization('ger', 'product rating', 'noReviewsInAllLang', 'Es gibt noch keine Bewertungen.');
        $this->setLocalization('eng', 'product rating', 'noReviewsInAllLang', 'There are no reviews.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('bewertung_alle_sprachen');

        $this->removeLocalization('reviewsInAllLang', 'product rating');
        $this->removeLocalization('noReviewsInAllLang', 'product rating');
    }
}
