<?php
/**
 * Update admin bootstrap template in database
 *
 * @author msc
 * @created Mon, 27 Aug 2018 09:11:16 +0200
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
class Migration_20180827091116 extends Migration implements IMigration
{
    protected $author = 'msc';
    protected $description = 'Update admin bootstrap template in database';

    public function up()
    {
        $db = Shop::Container()->getDB();
        $db->query("UPDATE `ttemplate` SET
`cTemplate` = 'bootstrap',
`eTyp` = 3,
`parent` = NULL,
`name` = 'bootstrap',
`author` = 'JTL-Software-GmbH',
`url` = 'https://www.jtl-software.de',
`version` = '1.0.0',
`preview` = 'preview.png'
WHERE `cTemplate` = 'bootstrap' AND `eTyp` = 'admin'
LIMIT 1;", \DB\ReturnType::DEFAULT);
    }

    public function down()
    {
    }
}