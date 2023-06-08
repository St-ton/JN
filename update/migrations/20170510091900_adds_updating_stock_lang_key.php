<?php
/**
 * adds updating stock lang key
 *
 * @author ms
 * @created Wed, 10 May 2017 09:19:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20170510091900
 */
class Migration_20170510091900 extends Migration implements IMigration
{
    protected $author      = 'ms';
    protected $description = 'Add updating stock lang key';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'productDetails', 'updatingStockInformation', 'Lagerinformationen für Variationen werden geladen');
        $this->setLocalization('eng', 'productDetails', 'updatingStockInformation', 'updating stock information');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeLocalization('updatingStockInformation');
    }
}
