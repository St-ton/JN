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
            'anonymize_ip_mask_v4',                                                                 // setting name
            '255.255.255.0',                                                                        // default value of setting
            CONF_GLOBAL,                                                                            // section of setting (see: includes / defines_inc.php)
            'IPv4-Adress-Anonymisiermaske',                                                         // caption of setting in the backend
            'text',                                                                                 // setting-type
            571,                                                                                    // order-position
             (object) [
                 'cBeschreibung' => 'IP-Maske zum Anonymisieren der IP-Adressen von Besuchern<br>'.
                                    '(z.B.: 82.54.123.42 wird zu 82.54.123.0)'
            ],
            true
        );
        $this->setConfig(
            'anonymize_ip_mask_v6',                                                                 // setting name
            'ffff:ffff:ffff:ffff:0000:0000:0000:0000',                                              // default value of setting
            CONF_GLOBAL,                                                                            // section of setting (see: includes / defines_inc.php)
            'IPv6-Adress-Anonymisiermaske',                                                         // caption of setting in the backend
            'text',                                                                                 // setting-type
            572,                                                                                    // order-position
            (object) [
                'cBeschreibung' => 'IP-Maske zum Anonymisieren der IP-Adressen von Besuchern<br>'.
                                   '(z.B.: 2001:0db8:85a3:08d3:1319:8a2e:0370:7347 wird zu 2001:db8:85a3:8d3:0:0:0:0)'
            ],
            true
        );

        $this->execute('
            CREATE TABLE IF NOT EXISTS `tanonymizer`(
                  `kAnonymizer` INT NOT NULL AUTO_INCREMENT
                , `dLastRun` datetime
                , PRIMARY KEY `kAnonymizer` (`kAnonymizer`)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8
        ');
    }

    public function down()
    {
        $this->execute('drop table `tanonymizer`');

        $this->removeConfig('ip_anonymize_mask_v4');
        $this->removeConfig('ip_anonymize_mask_v6');
    }
}
