<?php
/**
 * Update tjtllog.nLevel to INT
 *
 * @author Felix Moche
 * @created Mon, 12 Mar 2018 15:41:00 +0100
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
class Migration_20180312154100 extends Migration implements IMigration
{
    protected $author = 'Felix Moche';
    protected $description = 'Update tjtllog.nLevel to INT';

    public function up()
    {
        $this->execute("ALTER TABLE `tjtllog` CHANGE COLUMN `nLevel` `nLevel` INT UNSIGNED NOT NULL");
    }

    public function down()
    {
        $this->execute("ALTER TABLE `tjtllog` CHANGE COLUMN `nLevel` `nLevel` TINYINT UNSIGNED NOT NULL");
    }
}
