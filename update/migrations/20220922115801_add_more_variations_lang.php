<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

class Migration_20220922115801 extends Migration implements IMigration
{
    public function up()
    {
        $this->setLocalization('ger', 'productOverview', 'moreVariationsAvailable','Weitere Variationen erhältlich.');
        $this->setLocalization('eng', 'productOverview', 'moreVariationsAvailable','More variations available.');
    }

    public function down()
    {
        $this->removeLocalization('moreVariationsAvailable', 'productOverview');
    }
}
