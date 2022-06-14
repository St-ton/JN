<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220614091900
 */
class Migration_20220614091900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add verified purchase lang';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'product rating', 'verifiedPurchase', 'Verifizierter Kauf');
        $this->setLocalization('eng', 'product rating', 'verifiedPurchase', 'Verified purchase');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->removeLocalization('verifiedPurchase', 'product rating');
    }
}
