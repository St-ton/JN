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

        $this->setLocalization('ger', 'product rating', 'verifiedPurchaseNotice', 'Bewertungen, die mit „Verifizierter Kauf“ gekennzeichnet sind, stammen von Kunden, die den Artikel nachweislich in diesem Onlineshop erworben haben.');
        $this->setLocalization('eng', 'product rating', 'verifiedPurchaseNotice', 'Reviews marked as \"Verified purchase\" were written by customers who verifiably purchased the item from this online shop.');

        $this->setLocalization('ger', 'product rating', 'reviewsHowTo', 'Wie funktionieren Bewertungen?');
        $this->setLocalization('eng', 'product rating', 'reviewsHowTo', 'How do reviews work?');
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function down()
    {
        $this->removeLocalization('verifiedPurchase', 'product rating');
        $this->removeLocalization('verifiedPurchaseNotice', 'product rating');
        $this->removeLocalization('reviewsHowTo', 'product rating');
    }
}
