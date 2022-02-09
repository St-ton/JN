<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220208155300
 */
class Migration_20220208155300 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Add manufacturer status flag';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `thersteller` ADD `nAktiv` TINYINT(1) NOT NULL DEFAULT 1');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `thersteller` DROP COLUMN `nAktiv`');
    }
}
