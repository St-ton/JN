<?php
/**
 * Plugin bootstrap flag
 *
 * @author andy
 * @created Mon, 13 Jun 2016 15:51:56 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20160613155156
 */
class Migration_20160613155156 extends Migration implements IMigration
{
    protected $author = 'andy';

    public function up()
    {
        $this->execute("ALTER TABLE `tplugin` ADD `bBootstrap` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
    }

    public function down()
    {
        $this->dropColumn('tplugin', 'bBootstrap');
    }
}
