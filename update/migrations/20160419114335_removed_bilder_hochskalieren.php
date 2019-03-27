<?php
/**
 * removed bilder_hochskalieren
 *
 * @author andy
 * @created Tue, 19 Apr 2016 11:43:35 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160419114335
 */
class Migration_20160419114335 extends Migration implements IMigration
{
    protected $author = 'andy';

    public function up()
    {
        $this->removeConfig('bilder_hochskalieren');
    }

    public function down()
    {
    }
}
