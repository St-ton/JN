<?php
/**
 * Move language variables "invalidHash" und "invalidCustomer" to account data
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
class Migration_20171215121900 extends Migration implements IMigration
{
    protected $author      = 'Franz Gotthardt';
    protected $description = 'Move language variables "invalidHash" und "invalidCustomer" to account data';

    public function up()
    {
        Shop::DB()->update('tsprachwerte', 'cName', 'invalidHash', (object)["kSprachsektion" => 6]);
        Shop::DB()->update('tsprachwerte', 'cName', 'invalidCustomer', (object)["kSprachsektion" => 6]);
    }

    public function down()
    {
        Shop::DB()->update('tsprachwerte', 'cName', 'invalidHash', (object)["kSprachsektion" => 4]);
        Shop::DB()->update('tsprachwerte', 'cName', 'invalidCustomer', (object)["kSprachsektion" => 4]);
    }
}
