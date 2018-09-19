<?php
/**
 * correct_lang_var_product_available
 *
 * @author mh
 * @created Mon, 10 Sep 2018 12:16:47 +0200
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
class Migration_20180910121647 extends Migration implements IMigration
{
    protected $author = 'mh';
    protected $description = 'Correct lang var productAvailable';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'productAvailable', 'verfügbar');
    }

    public function down()
    {
        $this->setLocalization('ger', 'global', 'productAvailable', 'Artikel verfügbar ab');
    }
}