<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220105082500
 */
class Migration_20220105082500 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Better shipping country cost note';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'shippingInfoIcon', '(%s - Ausland abweichend)');
        $this->setLocalization('eng', 'productDetails', 'shippingInfoIcon', '(%s - int. shipments may differ)');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'productDetails', 'shippingInfoIcon', 'Ausland');
        $this->setLocalization('eng', 'productDetails', 'shippingInfoIcon', 'Other countries');
    }
}
