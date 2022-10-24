<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

class Migration_20221019150600 extends Migration implements IMigration
{
    public function up()
    {
        $this->setLocalization('ger', 'wishlist', 'infoItemsFound','%s Artikel wurden zu Ihrer Suche gefunden.');
        $this->setLocalization('eng', 'wishlist', 'infoItemsFound','%s products found.');
    }

    public function down()
    {
        $this->removeLocalization('infoItemsFound', 'wishlist');
    }
}
