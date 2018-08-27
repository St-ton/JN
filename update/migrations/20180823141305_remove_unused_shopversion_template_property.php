<?php
/**
 * remove_unused_shopversion_template_property
 *
 * @author msc
 * @created Thu, 23 Aug 2018 14:13:05 +0200
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
class Migration_20180823141305 extends Migration implements IMigration
{
    protected $author = 'msc';
    protected $description = "Remove unused template property 'shopversion'";

    public function up()
    {
        $this->dropColumn('ttemplate', 'shopversion');
    }

    public function down()
    {
        $db = Shop::Container()->getDB();
        $db->query('ALTER TABLE `ttemplate` ADD `shopversion` int(11) DEFAULT NULL AFTER `version`', \DB\ReturnType::DEFAULT);
    }
}