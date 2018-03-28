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
 * setLocalization    - add or update a localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180306160913 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'uploadInvalidFormat', 'Die Datei entspricht nicht dem geforderten Format');
        $this->setLocalization('ger', 'global', 'paginationOrderUsefulness', 'Hilfreich');
    }

    public function down()
    {
        $this->setLocalization('ger', 'global', 'uploadInvalidFormat', 'Die Datei entspricht nicht dem geforderte Format');
        $this->setLocalization('ger', 'global', 'paginationOrderUsefulness', 'Hilreich');
    }
}
