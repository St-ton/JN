<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20211217122300
 */
class Migration_20211217122300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add mini cart item overflow note';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'basket', 'itemOverflowNotice', 'Und %d weitere Artikel im <a href="%s">Warenkorb</a>.');
        $this->setLocalization('eng', 'basket', 'itemOverflowNotice', 'And %d more items in the <a href="%s">basket</a>.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('itemOverflowNotice', 'basket');
    }
}
