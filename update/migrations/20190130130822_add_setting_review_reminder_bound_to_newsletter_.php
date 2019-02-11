<?php
/**
 * add setting "review reminder bound to newsletter"
 *
 * @author Clemens Rudolph
 * @created Wed, 30 Jan 2019 13:08:22 +0100
 */

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
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 494 AND cWert = "N"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 3 WHERE kEinstellungenConf = 494 AND cWert = "Y"');
        $this->execute('INSERT INTO teinstellungenconfwerte VALUES(494, "", "B", 1)') ;
        $this->execute('UPDATE teinstellungen SET cWert = "B" WHERE cName = "bewertungserinnerung_nutzen"');
    }

    public function down()
    {
        $this->execute('DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf = 494 AND nSort = 1');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 1 WHERE kEinstellungenConf = 494 AND cWert = "Y"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 494 AND cWert = "N"');
        $this->execute('UPDATE teinstellungen SET cWert = "N" WHERE cName = "bewertungserinnerung_nutzen"');
    }
}
