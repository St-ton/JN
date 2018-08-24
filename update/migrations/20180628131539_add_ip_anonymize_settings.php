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

        // create the GDPR-timer table
        $this->execute('
            CREATE TABLE IF NOT EXISTS `tgdprtimers`(
                `cTimerName` varchar(128) DEFAULT "" NOT NULL COMMENT "a preferably uniq name of the timer",
                `dTimerLastRun` datetime DEFAULT NULL COMMENT "no 00:00:00 time possible; use NULL instead",
                PRIMARY KEY `TimerName` (`cTimerName`)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8
        ');

        // setting up the cron-job in the cron-table
        $oCronDataProtection = $this->fetchArray('SELECT * FROM `tcron` WHERE `cJobArt` = "dataprotection"');
        if (0 <= sizeof($oCronDataProtection)) {
            $this->execute('
                INSERT INTO `tcron`(`kKey`, `cKey`, `cJobArt`, `nAlleXStd`,`cTabelle`, `cName`, `dStart`, `dStartZeit`, `dLetzterStart`)
                    VALUES(50, "", "dataprotection", 24, "", "", now(), "00:00:00", now())
            ');
        }
    }

    public function down()
    {
        $oCronDataProtection = $this->fetchArray('SELECT * FROM `tcron` WHERE `cJobArt` = "dataprotection"');
        $this->execute('DELETE FROM `tcron` WHERE `kCron` = "'.$oCronDataProtection[0]['kCron'].'"');

        $this->execute('drop table `tgdprtimers`');

        $this->removeConfig('anonymize_ip_mask_v4');
        $this->removeConfig('anonymize_ip_mask_v6');
    }
}
