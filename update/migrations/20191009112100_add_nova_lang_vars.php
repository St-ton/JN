<?php
/**
 * Add nova lang vars
 *
 * @author mh
 * @created Wed, 9 Oct 2019 11:21:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191009112100
 */
class Migration_20191009112100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add nova lang vars';

    public function up()
    {
        $this->setLocalization('ger', 'global', 'selectChoose', 'Auswählen');
        $this->setLocalization('eng', 'global', 'selectChoose', 'Choose');
    }

    public function down()
    {
        $this->removeLocalization('selectChoose');
    }
}
