<?php
/**
 * removing un-used setting-values
 *
 * @author Clemens Rudolph
 * @created Thu, 24 May 2018 12:38:41 +0200
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
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180524123841 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';
    protected $description = 'removing un-used setting-values';

    public function up()
    {
        $this->execute('DELETE FROM `teinstellungenconfwerte` WHERE `kEinstellungenConf` = 1427 AND `cName` IN ("Normal", "Quellcode")')
    }

    public function down()
    {
        $this->execute('INSERT INTO `teinstellungenconfwerte` VALUES(1427, "Normal", "N", 0), (1427, "Quellcode", "Q", 1);')
    }
}
