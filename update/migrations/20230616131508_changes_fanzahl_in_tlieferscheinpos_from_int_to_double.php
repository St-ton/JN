<?php declare(strict_types=1);
/**
 * Changes fAnzahl in tlieferscheinpos from INT to DOUBLE
 *
 * @author Tim Niko Tegtmeyer
 * @created Fri, 16 Jun 2023 13:15:08 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230616131508
 */
class Migration_20230616131508 extends Migration implements IMigration
{
    protected $author = 'Tim Niko Tegtmeyer';
    protected $description = 'Changes fAnzahl in tlieferscheinpos from INT to DOUBLE';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute('ALTER TABLE `tlieferscheinpos` CHANGE COLUMN `fAnzahl` `fAnzahl` DOUBLE(10,4) UNSIGNED NOT NULL');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('ALTER TABLE `tlieferscheinpos` CHANGE COLUMN `fAnzahl` `fAnzahl` INT(10) UNSIGNED NOT NULL');
    }
}
