<?php
/**
 * Remove tpreise
 *
 * @author fp
 * @created Mon, 15 Apr 2019 16:43:25 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190415164325
 */
class Migration_20190415164325 extends Migration implements IMigration
{
    protected $author = 'Falk Prüfer';
    protected $description = 'Remove tpreise';

    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS tpreise');
        $this->execute('DROP INDEX kArtikel ON tpreis');
        $this->execute('CREATE UNIQUE INDEX kArtikel on tpreis(kArtikel, kKundengruppe, kKunde)');
        $this->execute('DROP INDEX kArtikel ON tpreisverlauf');
        $this->execute('CREATE UNIQUE INDEX kArtikel on tpreisverlauf(kArtikel, kKundengruppe, dDate)');
        $this->execute('UPDATE tpreis SET kKunde = 0 WHERE kKunde IS NULL');
        $this->execute('CREATE UNIQUE INDEX kPreis_nAnzahlAb ON tpreisdetail(kPreis, nAnzahlAb)');
    }

    public function down()
    {
        $this->execute('DROP INDEX kPreis_nAnzahlAb ON tpreisdetail');
        $this->execute('UPDATE tpreis SET kKunde = NULL WHERE kKunde = 0');
        $this->execute('DROP INDEX kArtikel ON tpreisverlauf');
        $this->execute('CREATE INDEX kArtikel on tpreisverlauf(kArtikel, kKundengruppe, dDate)');
        $this->execute('DROP INDEX kArtikel ON tpreis');
        $this->execute('CREATE INDEX kArtikel on tpreis(kArtikel, kKundengruppe, kKunde)');
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
