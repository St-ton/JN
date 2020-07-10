<?php
/**
 * Remove unused settings
 *
 * @author mh
 * @created Fr, 12 Jun 2020 15:00:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200710094300
 */
class Migration_20200710094300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Remove unused settings';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->removeConfig('news_kategorie_boxanzeigen');
        $this->removeConfig('news_sicherheitscode');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->setConfig(
            'news_kategorie_boxanzeigen',
            'Y',
            \CONF_NEWS,
            'Newskategorien in einer Box anzeigen',
            'selectbox',
            110,
            (object)[
                'cBeschreibung' => 'Möchten Sie eine Übersicht aller Newskategorien in einer Box angezeigt haben?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
        $this->setConfig(
            'news_sicherheitscode',
            'Y',
            \CONF_NEWS,
            'Spamschutz aktivieren',
            'selectbox',
            115,
            (object)[
                'cBeschreibung' => 'Soll beim Erstellen eines Kommentares ein Sicherheitscode abgefragt werden, damit das Formular akzeptiert und abgesendet wird?',
                'inputOptions'  => [
                    'Y' => 'Ja',
                    'N' => 'Nein',
                ],
            ]
        );
    }
}
