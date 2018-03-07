<?php
/**
 * change wrong language-variable-values
 *
 * @author Clemens Rudolph
 * @created Tue, 06 Mar 2018 16:09:13 +0100
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
class Migration_20180306160913 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';

    public function up()
    {
        $this->execute("UPDATE `tsprachwerte` SET `cWert` = 'Die Datei entspricht nicht dem geforderten Format', `cStandard`='Die Datei entspricht nicht dem geforderten Format' WHERE `kSprachISO` = 1 AND `cName` = 'uploadInvalidFormat'");
        $this->execute("UPDATE `tsprachwerte` SET `cWert` = 'Hilfreich', `cStandard`='Hilfreich' WHERE `kSprachISO` = 1 AND `cName` = 'paginationOrderUsefulness'");
    }

    public function down()
    {
        $this->execute("UPDATE `tsprachwerte` SET `cWert` = 'Die Datei entspricht nicht dem geforderte Format', `cStandard`='Die Datei entspricht nicht dem geforderte Format' WHERE `kSprachISO` = 1 AND `cName` = 'uploadInvalidFormat'");
        $this->execute("UPDATE `tsprachwerte` SET `cWert` = 'Hilreich', `cStandard`='Hilreich' WHERE `kSprachISO` = 1 AND `cName` = 'paginationOrderUsefulness'");
    }
}
