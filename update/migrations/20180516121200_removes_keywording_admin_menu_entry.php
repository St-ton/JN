<?php
/**
 * removed keywording admin menu entry
 *
 * @author fm
 * @created Wed, 16 May 2018 13:13:00 +0200
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
class Migration_20180516121200 extends Migration implements IMigration
{
    protected $author = 'fm';

    public function up()
    {
        $this->execute("DELETE FROM tadminmenu WHERE cURL = 'keywording.php'");
    }

    public function down()
    {
        $this->execute("INSERT INTO `tadminmenu` 
            (`kAdminmenu`, `kAdminmenueGruppe`, `cModulId`, `cLinkname`, `cURL`, `cRecht`, `nSort`) 
            VALUES (8,7,'core_jtl','Meta-Keywords Blacklist','keywording.php','SETTINGS_META_KEYWORD_BLACKLIST_VIEW', 20)");
    }
}
