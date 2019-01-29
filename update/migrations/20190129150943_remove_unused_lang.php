<?php
/**
 * remove_unused_lang
 *
 * @author mh
 * @created Tue, 29 Jan 2019 15:09:43 +0100
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
class Migration_20190129150943 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'remove unused lang variables';

    public function up()
    {
        $this->removeLocalization('goToContactForm');
        $this->removeLocalization('payWithUosCc');
        $this->removeLocalization('payWithUosDd');
        $this->removeLocalization('acceptAgb');
        $this->removeLocalization('available');
        $this->execute('DELETE FROM tsprachwerte WHERE cName="next" AND `kSprachsektion`=12');
        $this->execute('DELETE FROM tsprachwerte WHERE cName="previous" AND `kSprachsektion`=12');
    }

    public function down()
    {
    }
}
