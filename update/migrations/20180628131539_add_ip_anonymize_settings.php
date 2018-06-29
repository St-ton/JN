<?php
/**
 * add_ip_anonymize_settings
 *
 * @author Clemens Rudolph
 * @created Thu, 28 Jun 2018 13:15:39 +0200
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
class Migration_20180628131539 extends Migration implements IMigration
{
    protected $author      = 'Clemens Rudolph';
    protected $description = 'add_ip_anonymize_settings';

    public function up()
    {
        $this->setConfig(
            'ip_anonymize_mask_v4',                                                                // setting name
            '255.255.255.0',                                                                         // default value of setting
            CONF_GLOBAL,                                                                           // section of setting (see: includes / defines_inc.php)
            'IPv4-Adress-Anonymisiermaske',                                                        // caption of setting in the backend
            'text',                                                                                // setting-type
            571,                                                                                   // order-position
             (object) [
                'cBeschreibung' => 'IP-Maske zum anonymisieren der IP-Adresse des Eink&auml;ufers'
            ],
            true
        );
        $this->setconfig(
            'ip_anonymize_mask_v6',                                                                // setting name
            'ffff:ffff:ffff:ffff:0000:0000:0000:0000',                                             // default value of setting
            CONF_GLOBAL,                                                                           // section of setting (see: includes / defines_inc.php)
            'IPv6-Adress-Anonymisiermaske',                                                        // caption of setting in the backend
            'text',                                                                                // setting-type
            572,                                                                                   // order-position
            (object) [
                'cBeschreibung' => 'IP-Maske zum anonymisieren der IP-Adresse des Eink&auml;ufers'
            ],
            true
        );
    }

    public function down()
    {
        $this->removeConfig('ip_anonymize_mask_v4');
        $this->removeConfig('ip_anonymize_mask_v6');
    }
}
