<?php declare(strict_types=1);
/**
 * Remove tpreise pre
 *
 * @author fp
 * @created Mon, 15 Apr 2019 16:43:25 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190415164300
 */
class Migration_20190415164300 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Remove tpreise pre';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS tpreise');
        $this->execute('ANALYZE TABLE tpreis');
        $this->execute('ANALYZE TABLE tpreisverlauf');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            'CREATE TABLE tpreise (
                kKundengruppe   INT UNSIGNED DEFAULT 0  NOT NULL,
                kArtikel        INT UNSIGNED DEFAULT 0  NOT NULL,
                fVKNetto        DOUBLE                      NULL,
                nAnzahl1        INT UNSIGNED                NULL,
                nAnzahl2        INT UNSIGNED                NULL,
                nAnzahl3        INT UNSIGNED                NULL,
                nAnzahl4        INT UNSIGNED                NULL,
                nAnzahl5        INT UNSIGNED                NULL,
                fPreis1         DOUBLE                      NULL,
                fPreis2         DOUBLE                      NULL,
                fPreis3         DOUBLE                      NULL,
                fPreis4         DOUBLE                      NULL,
                fPreis5         DOUBLE                      NULL,
                PRIMARY KEY (kArtikel, kKundengruppe),
                KEY fVKNetto (fVKNetto)
            ) ENGINE = InnoDB COLLATE = utf8_unicode_ci'
        );
    }
}
