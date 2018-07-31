<?php
/**
 * Remove marketplace admin menu entry
 *
 * @author Danny Raufeisen
 */

class Migration_20180731094600 extends Migration implements IMigration
{
    protected $author      = 'Danny Raufeisen';
    protected $description = 'Remove marketplace admin menu entry';

    public function up()
    {
        $this->execute("DELETE FROM tadminmenu WHERE cURL = 'marktplatz.php'");
    }

    public function down()
    {
        $this->execute(
            "INSERT INTO tadminmenu (kAdminmenu, kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort)
                VALUES (81, 5, 'core_jtl', 'Marktplatz', 'marktplatz.php', 'PLUGIN_ADMIN_VIEW', 80)"
        );
    }
}
