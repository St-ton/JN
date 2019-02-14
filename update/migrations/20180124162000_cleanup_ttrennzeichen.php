<?php
/**
 * Rebuild ttrennzeichen and add unique index
 *
 * @author Felix Moche
 * @created Wed, 18 Jan 2018 16:20:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Catalog\Trennzeichen;

/**
 * Class Migration_20180124162000
 */
class Migration_20180124162000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Rebuild ttrennzeichen and add unique index';

    public function up()
    {
        Trennzeichen::migrateUpdate();
        $this->execute('ALTER TABLE `ttrennzeichen` ADD UNIQUE INDEX `unique_lang_unit` (`kSprache`, `nEinheit`)');
    }

    public function down()
    {
        $this->execute('ALTER TABLE `ttrennzeichen` DROP INDEX `unique_lang_unit`');
    }
}
