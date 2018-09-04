<?php
/**
 * changes optional fill out hint
 *
 * @author ms
 * @created Tue, 04 Sep 2018 11:48:00 +0200
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
class Migration_20180904114800 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'changes optional fill out hint';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->removeLocalization('conditionalFillOut');
        $this->setLocalization('ger', 'checkout', 'optional', 'optionale Angabe');
        $this->setLocalization('eng', 'checkout', 'optional', 'optional');
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    public function down()
    {
        $this->removeLocalization('optional');
        $this->setLocalization('ger', 'checkout', 'conditionalFillOut', 'optionale Angabe');
        $this->setLocalization('eng', 'checkout', 'conditionalFillOut', 'conditional fill in');
    }
}
