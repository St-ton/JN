<?php
/**
 * Lang variables misc law fixes
 *
 * @author mh
 * @created Mon, 27 July 2020 15:01:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200727150100
 */
class Migration_20200727150100 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Lang variables misc law fixes';

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function up()
    {
        //SHOP-4259
        $this->setLocalization('ger', 'global', 'shippingTime', 'Lieferzeit');
        $this->setLocalization('eng', 'global', 'shippingTime', 'Delivery time');

        //SHOP-4261
        $this->setLocalization('ger', 'productDetails', 'suggestedPrice', 'Unverbindliche Preisempfehlung des Herstellers');
        $this->setLocalization('eng', 'productDetails', 'suggestedPrice', 'Manufacturers recommended retail price');
        $this->removeLocalization('suggestedPriceExpl', 'productDetails');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        //SHOP-4259
        $this->setLocalization('ger', 'global', 'shippingTime', 'Errechnete Lieferzeit');
        $this->setLocalization('eng', 'global', 'shippingTime', 'Calculated delivery time');

        //SHOP-4261
        $this->setLocalization('ger', 'productDetails', 'suggestedPrice', 'UVP des Herstellers');
        $this->setLocalization('ger', 'productDetails', 'suggestedPriceExpl', '** Unverbindliche Preisempfehlung');
        $this->setLocalization('eng', 'productDetails', 'suggestedPrice', 'Manufacturers RRP');
        $this->setLocalization('eng', 'productDetails', 'suggestedPriceExpl', '** Recommended retail price');
    }
}
