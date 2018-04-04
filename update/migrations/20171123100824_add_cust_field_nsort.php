<?php
/**
 * Add customer-fields nSort
 *
 * @author Clemens Rudolph
 * @created Thu, 23 Nov 2017 10:08:24 +0100
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
class Migration_20171123100824 extends Migration implements IMigration
{
    protected $author      = 'cr';
    protected $description = 'Add customer-fields nSort';

    public function up()
    {
        $this->execute('
            ALTER TABLE
                `tkundenfeldwert`
            ADD COLUMN
                `nSort` int(10) unsigned NOT NULL AFTER `cWert`
        ');
    }

    public function down()
    {
        $this->execute('
            ALTER TABLE
                `tkundenfeldwert`
            DROP COLUMN
                `nSort`;
        ');
    }
}
