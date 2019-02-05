<?php
/**
 * add setting "review reminder bound to newsletter"
 *
 * @author Clemens Rudolph
 * @created Wed, 30 Jan 2019 13:08:22 +0100
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
class Migration_20190130130822 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';
    protected $description = 'add setting "review reminder bound to newsletter"';

    public function up()
    {
    }

    public function down()
    {
    }
}
