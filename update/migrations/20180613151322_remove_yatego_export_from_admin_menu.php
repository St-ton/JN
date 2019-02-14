<?php
/**
 * remove yatego export from admin menu
 *
 * @author mh
 * @created Wed, 13 Jun 2018 15:13:22 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Migration
 *
 * Available methods:
 * execute            - returns affected rows
 * fetchOne           - single fetched object
 * fetchAll           - array of fetched objects
 * fetchArray         - array of fetched assoc arrays
 * dropColumn         - drops a column if exists
 * addLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180613151322 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Removes Yatego Export from admin menu';

    public function up()
    {
        $this->execute("DELETE FROM tadminmenu WHERE cURL = 'yatego.export.php'");
        $this->execute("DELETE FROM tadminrecht WHERE cRecht = 'EXPORT_YATEGO_VIEW'");
        $this->execute("DELETE FROM tadminrechtegruppe WHERE cRecht = 'EXPORT_YATEGO_VIEW'");
    }

    public function down()
    {
        $this->execute("INSERT INTO `tadminmenu` 
            (`kAdminmenu`, `kAdminmenueGruppe`, `cModulId`, `cLinkname`, `cURL`, `cRecht`, `nSort`) 
            VALUES (46, 12, 'core_jtl', 'Yatego Export', 'yatego.export.php', 'EXPORT_YATEGO_VIEW', 70)");
        $this->execute("INSERT INTO `tadminrecht` 
            (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) 
            VALUES ('EXPORT_YATEGO_VIEW', 'Yatego Export', 7)");
    }
}
