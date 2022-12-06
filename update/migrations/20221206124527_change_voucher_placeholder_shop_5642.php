<?php declare(strict_types=1);
/**
 * Change Voucher placeholder SHOP-5642
 *
 * @author sl
 * @created Tue, 06 Dec 2022 12:45:27 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20221206124527
 */
class Migration_20221206124527 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'Change Voucher placeholder SHOP-5642';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'voucherFlexPlaceholder', 'Gutscheinwert');
        $this->setLocalization('eng', 'productDetails', 'voucherFlexPlaceholder', 'Voucher value');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function down()
    {
        $this->setLocalization('ger', 'productDetails', 'voucherFlexPlaceholder', 'Gutscheinwert in %s');
        $this->setLocalization('eng', 'productDetails', 'voucherFlexPlaceholder', 'Voucher value in %s');
    }
}
