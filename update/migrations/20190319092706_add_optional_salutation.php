<?php
/**
 * add_optional_salutation
 *
 * @author mh
 * @created Tue, 19 Mar 2019 09:27:06 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20190319092706 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add optional salutation';

    public function up()
    {
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 3
                WHERE teinstellungenconf.cWertName='kundenregistrierung_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'N'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 2
                WHERE teinstellungenconf.cWertName='kundenregistrierung_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'Y'"
        );
        $this->execute("INSERT INTO teinstellungenconfwerte VALUES(
                  (SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'kundenregistrierung_abfragen_anrede'),
                  'Ja, optionale Angabe', 'O', 1)");
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 3
                WHERE teinstellungenconf.cWertName='lieferadresse_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'N'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 2
                WHERE teinstellungenconf.cWertName='lieferadresse_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'Y'"
        );
        $this->execute("INSERT INTO teinstellungenconfwerte VALUES(
                  (SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'lieferadresse_abfragen_anrede'),
                  'Ja, optionale Angabe', 'O', 1)");
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 3
                WHERE teinstellungenconf.cWertName='kontakt_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'N'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 2
                WHERE teinstellungenconf.cWertName='kontakt_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'Y'"
        );
        $this->execute("INSERT INTO teinstellungenconfwerte VALUES(
                  (SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'kontakt_abfragen_anrede'),
                  'Ja, optionale Angabe', 'O', 1)");
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 3
                WHERE teinstellungenconf.cWertName='produktfrage_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'N'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 2
                WHERE teinstellungenconf.cWertName='produktfrage_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'Y'"
        );
        $this->execute("INSERT INTO teinstellungenconfwerte VALUES(
                  (SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'produktfrage_abfragen_anrede'),
                  'Ja, optionale Angabe', 'O', 1)");
    }

    public function down()
    {
        $this->execute(
            "DELETE teinstellungenconfwerte
              FROM teinstellungenconfwerte 
              JOIN teinstellungenconf USING(kEinstellungenConf)
              WHERE teinstellungenconf.cWertName='kundenregistrierung_abfragen_anrede'
                AND teinstellungenconfwerte.nSort = 1"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 2
                WHERE teinstellungenconf.cWertName='kundenregistrierung_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'N'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 1
                WHERE teinstellungenconf.cWertName='kundenregistrierung_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'Y'"
        );

        $this->execute(
            "DELETE teinstellungenconfwerte
              FROM teinstellungenconfwerte 
              JOIN teinstellungenconf USING(kEinstellungenConf)
              WHERE teinstellungenconf.cWertName='lieferadresse_abfragen_anrede'
                AND teinstellungenconfwerte.nSort = 1"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 2
                WHERE teinstellungenconf.cWertName='lieferadresse_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'N'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 1
                WHERE teinstellungenconf.cWertName='lieferadresse_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'Y'"
        );

        $this->execute(
            "DELETE teinstellungenconfwerte
              FROM teinstellungenconfwerte 
              JOIN teinstellungenconf USING(kEinstellungenConf)
              WHERE teinstellungenconf.cWertName='kontakt_abfragen_anrede'
                AND teinstellungenconfwerte.nSort = 1"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 2
                WHERE teinstellungenconf.cWertName='kontakt_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'N'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 1
                WHERE teinstellungenconf.cWertName='kontakt_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'Y'"
        );

        $this->execute(
            "DELETE teinstellungenconfwerte
              FROM teinstellungenconfwerte 
              JOIN teinstellungenconf USING(kEinstellungenConf)
              WHERE teinstellungenconf.cWertName='produktfrage_abfragen_anrede'
                AND teinstellungenconfwerte.nSort = 1"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 2
                WHERE teinstellungenconf.cWertName='produktfrage_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'N'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte
                JOIN teinstellungenconf USING(kEinstellungenConf)
                SET teinstellungenconfwerte.nSort = 1
                WHERE teinstellungenconf.cWertName='produktfrage_abfragen_anrede'
                    AND teinstellungenconfwerte.cWert = 'Y'"
        );
    }
}
