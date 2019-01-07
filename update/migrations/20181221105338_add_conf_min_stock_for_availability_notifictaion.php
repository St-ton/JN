<?php
/**
 * add_conf_min_stock_for_availability_notifictaion
 *
 * @author mh
 * @created Fri, 21 Dec 2018 10:53:38 +0100
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
class Migration_20181221105338 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'add conf min stock for availability notifictaion';

    public function up()
    {
        $this->setConfig(
            'benachrichtigung_min_lagernd',
            0,
            5,
            'Mindestlagerbestand für Benachrichtigung',
            'number',
            745
        );
    }

    public function down()
    {
        $this->removeConfig('benachrichtigung_min_lagernd');
    }
}
