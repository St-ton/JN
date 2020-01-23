<?php

/**
 * adds config lang var
 *
 * @author ms
 * @created Tue, 23 Jan 2020 12:25:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200123122500
 */
class Migration_20200123122500 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'adds config lang var';

    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'applyConfiguration', 'Konfiguration übernehmen');
        $this->setLocalization('eng', 'productDetails', 'applyConfiguration', 'apply configuration');

        $this->setLocalization('ger', 'productDetails', 'saveConfiguration', 'Speichern');
        $this->setLocalization('eng', 'productDetails', 'saveConfiguration', 'Save');
    }

    public function down()
    {
        $this->removeLocalization('applyConfiguration');
        $this->removeLocalization('saveConfiguration');
    }
}
