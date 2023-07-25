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
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'basket', 'itemOverflowNotice', 'Es befinden sich %d weitere Artikel im <a href="%s">Warenkorb</a>.');
        $this->setLocalization('eng', 'basket', 'itemOverflowNotice', 'There are %d more items in your <a href="%s">basket</a>.');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeLocalization('itemOverflowNotice', 'basket');
    }
}
