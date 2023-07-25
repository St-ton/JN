<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20211220155900
 */
class Migration_20211220155900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Create rate limit table';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tfloodprotect` ADD COLUMN `reference` INT NULL DEFAULT NULL');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tfloodprotect` DROP COLUMN `reference`');
    }
}
