<?php
/**
 * Add language column to adminlogin table
 *
 * @author Danny Raufeisen
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Shop;

/**
 * Class Migration_20181130110200
 */
class Migration_20181130110200 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Add language column to adminlogin table';

    public function up()
    {
        $stdLang = (int)Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y')->kSprache;
        $this->execute("ALTER TABLE tadminlogin ADD COLUMN kSprache TINYINT(3) UNSIGNED DEFAULT $stdLang");
    }

    public function down()
    {
        $this->execute('ALTER TABLE tadminlogin DROP COLUMN kSprache');
    }
}
