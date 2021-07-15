<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20210715154900
 */
class Migration_20210715154900 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Better coupon description lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'checkout', 'useCoupon', 'Aktionscoupon einlösen');
        $this->setLocalization('eng', 'checkout', 'useCoupon', 'Redeem promotional coupon');
        $this->setLocalization('ger', 'checkout', 'couponFormDesc', 'Kostenfrei ausgestellten Aktionscoupon/Werbecoupon einlösen.');
        $this->setLocalization('eng', 'checkout', 'couponFormDesc', 'Redeem a free promotional coupon.');
        $this->setLocalization('ger', 'checkout', 'couponCodePlaceholder', 'Coupon-Code eingeben');
        $this->setLocalization('eng', 'checkout', 'couponCodePlaceholder', 'Enter coupon code');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'checkout', 'useCoupon', 'Coupon einlösen');
        $this->setLocalization('eng', 'checkout', 'useCoupon', 'Redeem coupon');

        $this->removeLocalization('couponFormDesc', 'checkout');
        $this->removeLocalization('couponCodePlaceholder', 'checkout');
    }
}
