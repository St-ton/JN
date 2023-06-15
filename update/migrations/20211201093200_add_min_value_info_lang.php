<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20211201093200
 */
class Migration_20211201093200 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add min value info lang';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'minValueInfo', 'Bitte beachten Sie den Mindestbestellwert von %s %s.');
        $this->setLocalization('eng', 'productDetails', 'minValueInfo', 'Please note our minimum order value of %2$s %1$s.');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->removeLocalization('minValueInfo', 'productDetails');
    }
}
