<?php
/**
 * changed some language-values for assets
 *
 * @author Clemens Rudolph
 * @created Fri, 20 Apr 2018 12:35:20 +0200
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
class Migration_20180420123520 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';
    protected $description = 'changed some language-values for assets';

    public function up()
    {
        $this->setLocalization('ger', 'account data', 'useCredit', 'Guthaben verrechnet');
    }

    public function down()
    {
        $this->setLocalization('ger', 'account data', 'useCredit', 'Guthaben verrechnen');
    }
}
