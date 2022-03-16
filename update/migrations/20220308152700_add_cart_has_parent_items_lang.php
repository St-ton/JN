<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220308152700
 */
class Migration_20220308152700 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add cart has parent items lang';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger',
            'checkout',
            'warningCartContainedParentItems',
            'Ihr Warenkorb enthielt Vaterartikel die nicht gekauft werden dürfen. Bitte prüfen sie die Warenkorbpositionen.'
        );
        $this->setLocalization('eng',
            'checkout',
            'warningCartContainedParentItems',
            'Your basket contained parent items which can not be purchased. Please check the line items in the basket.'
        );
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->removeLocalization('warningCartContainedParentItems', 'checkout');
    }
}
