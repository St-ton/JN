<?php
/**
 * Change kSprache column to store an IETF language tag
 *
 * @author Danny Raufeisen
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;
use JTL\Shop;

/**
 * Class Migration_20190319103100
 */
class Migration_20190319103100 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Change kSprache column to store an IETF language tag';

    public function up()
    {
        $this->execute('ALTER TABLE tadminlogin DROP COLUMN kSprache');
        $this->execute("ALTER TABLE tadminlogin ADD COLUMN language VARCHAR(35) DEFAULT 'de-DE'");
    }

    public function down()
    {
        $stdLang = (int)Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y')->kSprache;
        $this->execute("ALTER TABLE tadminlogin ADD COLUMN kSprache TINYINT(3) UNSIGNED DEFAULT $stdLang");
        $this->execute('ALTER TABLE tadminlogin DROP COLUMN language');
    }
}
