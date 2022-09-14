<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220817160900
 */
class Migration_20220817160900 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Parameter handling for redirects';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tredirect` ADD COLUMN `paramHandling` TINYINT(1) NOT NULL DEFAULT 0');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tredirect` DROP COLUMN `paramHandling`');
    }
}
