<?php
/**
 * used flag to new customer coupons
 *
 * @author ms
 * @created Wed, 22 Jun 2016 11:34:00 +0200
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
class Migration_20160622113400 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("ALTER TABLE `tkuponneukunde` ADD COLUMN `cVerwendet` CHAR(1) NOT NULL DEFAULT 'N' AFTER `dErstellt`;");
    }

    public function down()
    {
        $this->dropColumn('tkuponneukunde', 'cVerwendet');
    }
}
