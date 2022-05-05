<?php
/**
 * jtlshop406
 * 20220502151730_remove trustedshop_menu.php
 */

/**
 * Class Migration_20220502151730
 */
class Migration_20220502151730 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Remove TrustedShop Menu';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "DELETE FROM tadminmenu WHERE cURL IN ('trustedshops.php', 'premiumplugin.php?plugin_id=agws_ts_features')"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute(
            "INSERT INTO tadminmenu (kAdminmenu, kAdminmenueGruppe, cModulId, cLinkname, cURL, cRecht, nSort)
                VALUES (50, 16, 'core_jtl', 'Trusted Shops', 'trustedshops.php', 'ORDER_TRUSTEDSHOPS_VIEW', 230),
                       (84, 16, 'core_jtl', 'TrustedShops Trustbadge Reviews', 'premiumplugin.php?plugin_id=agws_ts_features', 'PLUGIN_ADMIN_VIEW', 315)"
        );
    }
}
