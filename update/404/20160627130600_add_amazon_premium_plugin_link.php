<?php
/**
 * Adds backend link for the amazon payments premium plugin
 *
 * @author fm
 * @created Mon, 27 Jun 2016 13:06:00 +0200
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
class Migration_20160627130600 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("INSERT INTO `tadminmenu` (`kAdminmenueGruppe`, `cModulId`, `cLinkname`, `cURL`, `cRecht`, `nSort`) VALUES ('18', 'core_jtl', 'Amazon Payments', 'premiumplugin.php?plugin_id=s360_amazon_lpa_shop4', 'PLUGIN_ADMIN_VIEW', '315')");
    }

    public function down()
    {
        $this->execute("DELETE FROM `tadminmenu` WHERE `nSort`=315 AND cRecht='PLUGIN_ADMIN_VIEW'");
    }
}
