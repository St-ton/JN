<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201015144700
 */
class Migration_20201015144700 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add topbar lang var';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'topbarNote', '');
        $this->setLocalization('eng', 'global', 'topbarNote', '');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeLocalization('topbarNote', 'global');
    }
}
