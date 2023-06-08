<?php
/**
 * add_conf_min_stock_for_availability_notifictaion
 *
 * @author mh
 * @created Fri, 21 Dec 2018 10:53:38 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20181221105338
 */
class Migration_20181221105338 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add conf min stock for availability notifictaion';

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('benachrichtigung_min_lagernd');
    }
}
