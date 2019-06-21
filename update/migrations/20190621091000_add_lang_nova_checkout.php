<?php
/**
 * Add lang nova checkout
 *
 * @author mh
 * @created Fri, 21 June 2019 09:10:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190621091000
 */
class Migration_20190621091000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang nova checkout';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'change', 'Ändern');
        $this->setLocalization('eng', 'global', 'change', 'Change');
    }

    public function down()
    {
        $this->removeLocalization('change');
    }
}
