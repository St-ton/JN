<?php
/**
 * add setting "review reminder bound to newsletter"
 *
 * @author Clemens Rudolph
 * @created Wed, 30 Jan 2019 13:08:22 +0100
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
class Migration_20190130130822 extends Migration implements IMigration
{
    protected $author      = 'Clemens Rudolph';
    protected $description = 'add setting "review reminder bound to newsletter"';

    public function up()
    {
        $this->execute(
            "UPDATE teinstellungenconfwerte w
            JOIN teinstellungenconf c
                ON c.kEinstellungenConf = w.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'N'
            SET w.nSort = 2"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte w
            JOIN teinstellungenconf c
                ON c.kEinstellungenConf = w.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'Y'
            SET w.nSort = 3"
        );
        $this->execute("INSERT INTO teinstellungenconfwerte(
                kEinstellungenConf,
                cName,
                cWert,
                nSort)
            VALUES(
                (SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'bewertungserinnerung_nutzen'),
                'An Newslettereinwilligung koppeln',
                'B',
                1)"
        );
    }

    public function down()
    {
        $this->execute("DELETE w FROM teinstellungenconfwerte w JOIN teinstellungenconf c
            WHERE w.kEinstellungenConf = c.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'B'"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte w
            JOIN teinstellungenconf c
                ON c.kEinstellungenConf = w.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'N'
            SET w.nSort = 2"
        );
        $this->execute(
            "UPDATE teinstellungenconfwerte w
            JOIN teinstellungenconf c
                ON c.kEinstellungenConf = w.kEinstellungenConf
                AND c.cWertName = 'bewertungserinnerung_nutzen'
                AND w.cWert = 'Y'
            SET w.nSort = 1"
        );
    }
}
