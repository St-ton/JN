<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201001121000
 */
class Migration_20201001121000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Remove nTransparenzfarbe from tbrandingeinstellung';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tbrandingeinstellung` DROP COLUMN `nTransparenzfarbe`');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tbrandingeinstellung` ADD COLUMN `nTransparenzfarbe` TINYINT UNSIGNED NOT NULL');
    }
}
