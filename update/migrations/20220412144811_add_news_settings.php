<?php declare(strict_types=1);
/**
 * Add News Settings
 *
 * @author rf
 * @created Tue, 12 Apr 2022 14:48:11 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220412144811
 */
class Migration_20220412144811 extends Migration implements IMigration
{
    protected $author = 'rf';
    protected $description = 'Add News Settings';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'news_kommentare_anzahl_antwort_kommentare_anzeigen',
            'Y',
            CONF_NEWS,
            'Zeige Anzahl der Antworten',
            'selectbox',
            110,
            (object)[
                'cBeschreibung' => 'Zeige die Anzahl der Antworten, in Klammern, neben der Kommentar-Anzahl an. Standard = Y',
                'inputOptions'  => [
                    'Y' => 'Anzeigen',
                    'N' => 'Ausblenden',
                ],
            ],
            true
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('news_kommentare_anzahl_antwort_kommentare_anzeigen');
    }
}
