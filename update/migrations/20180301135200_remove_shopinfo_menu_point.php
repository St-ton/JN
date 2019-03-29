<?php
/**
 * remove-shopinfo-menu-point
 *
 * @author Martin Schophaus
 * @created Thu, 01 Mar 2018 13:52:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180301135200
 */
class Migration_20180301135200 extends Migration implements IMigration
{
    protected $author      = 'Martin Schophaus';
    protected $description = 'remove-shopinfo-menu-point';

    public function up()
    {
        $this->execute("DELETE FROM tadminmenu WHERE cLinkname = 'Shopinfo (elm@ar)'");
    }

    public function down()
    {
        $this->execute("
          INSERT INTO tadminmenu (kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort)
          VALUES (12, 'core_jtl', 'Shopinfo (elm@ar)', 'shopinfoexport.php', 'EXPORT_SHOPINFO_VIEW', 40)          
        ");
    }
}
