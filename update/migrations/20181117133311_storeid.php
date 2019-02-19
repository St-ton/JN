<?php
/**
 * create store table
 *
 * @author aj
 * @created Mon, 17 Nov 2018 13:33:00 +0100
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
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20181117133311 extends Migration implements IMigration
{
    protected $author      = 'aj';
    protected $description = 'add plugin store id';

    public function up()
    {
        $this->execute('ALTER TABLE tplugin ADD COLUMN cStoreID varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL AFTER cPluginID');
    }

    public function down()
    {
        $this->dropColumn('tplugin', 'cStoreID');
    }
}
