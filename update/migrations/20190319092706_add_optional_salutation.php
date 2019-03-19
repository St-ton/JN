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
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 3 WHERE kEinstellungenConf = 12 AND cWert = "N"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 12 AND cWert = "Y"');
        $this->execute('INSERT INTO teinstellungenconfwerte VALUES(12, "Ja, optionale Angabe", "O", 1)') ;
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 3 WHERE kEinstellungenConf = 15 AND cWert = "N"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 15 AND cWert = "Y"');
        $this->execute('INSERT INTO teinstellungenconfwerte VALUES(15, "Ja, optionale Angabe", "O", 1)') ;
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 3 WHERE kEinstellungenConf = 302 AND cWert = "N"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 302 AND cWert = "Y"');
        $this->execute('INSERT INTO teinstellungenconfwerte VALUES(302, "Ja, optionale Angabe", "O", 1)') ;
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 3 WHERE kEinstellungenConf = 289 AND cWert = "N"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 289 AND cWert = "Y"');
        $this->execute('INSERT INTO teinstellungenconfwerte VALUES(289, "Ja, optionale Angabe", "O", 1)') ;
    }

    public function down()
    {
        $this->execute('DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf = 12 AND nSort = 1');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 12 AND cWert = "N"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 1 WHERE kEinstellungenConf = 12 AND cWert = "Y"');
        $this->execute('DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf = 15 AND nSort = 1');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 15 AND cWert = "N"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 1 WHERE kEinstellungenConf = 15 AND cWert = "Y"');
        $this->execute('DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf = 302 AND nSort = 1');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 302 AND cWert = "N"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 1 WHERE kEinstellungenConf = 302 AND cWert = "Y"');
        $this->execute('DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf = 289 AND nSort = 1');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 2 WHERE kEinstellungenConf = 289 AND cWert = "N"');
        $this->execute('UPDATE teinstellungenconfwerte SET nSort = 1 WHERE kEinstellungenConf = 289 AND cWert = "Y"');
    }
}
