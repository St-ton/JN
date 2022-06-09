<?php declare(strict_types=1);
/**
 * Add noDiscount to price table
 *
 * @author fp
 * @created Mon, 09 May 2022 11:50:06 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220509115006
 */
class Migration_20220509115006 extends Migration implements IMigration
{
    protected $author = 'fp';
    protected $description = 'Add noDiscount to price table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE tpreis ADD COLUMN noDiscount INT DEFAULT 0');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE tpreis DROP COLUMN noDiscount');
    }
}
