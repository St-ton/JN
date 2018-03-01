<?php
/**
 * Remove preisanzeige admin menu entry
 *
 * @author Felix Moche
 * @created Wed, 28 Feb 2018 19:35:00 +0100
 */

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
class Migration_20180228193500 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Remove preisanzeige admin menu entry';

    public function up()
    {
        $this->execute("DELETE FROM tadminmenu WHERE cURL = 'preisanzeige.php'");
        $this->execute("DELETE FROM tadminrecht WHERE cRecht = 'DISPLAY_PRICECHART_VIEW'");
        $this->execute("DELETE FROM teinstellungen WHERE kEinstellungenSektion = 118");
    }

    public function down()
    {
        $this->execute("INSERT INTO tadminmenu (kAdminmenu, kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort) VALUES (22,13,'core_jtl','Preisanzeige','preisanzeige.php','DISPLAY_PRICECHART_VIEW',120)");
        $this->execute("INSERT INTO tadminrecht (cRecht, cBeschreibung, kAdminrechtemodul) VALUES ('DISPLAY_PRICECHART_VIEW','Preisanzeige',3)");
    }
}
