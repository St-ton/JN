<?php
/**
 * Change database version to semantic versioning
 *
 * @author msc
 * @created Thu, 23 Aug 2018 15:48:32 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Shop;
use JTL\DB\ReturnType;

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
class Migration_20180823154832 extends Migration implements IMigration
{
    protected $author      = 'msc';
    protected $description = 'Change database version to semantic versioning';

    public function up()
    {
        $db = Shop::Container()->getDB();
        $db->query('ALTER TABLE `tversion` CHANGE `nVersion` `nVersion` varchar(20) NOT NULL', ReturnType::DEFAULT);
    }

    public function down()
    {
        $db = Shop::Container()->getDB();
        $db->query('ALTER TABLE `tversion` CHANGE `nVersion` `nVersion` int(10) DEFAULT NULL', ReturnType::DEFAULT);
    }
}
