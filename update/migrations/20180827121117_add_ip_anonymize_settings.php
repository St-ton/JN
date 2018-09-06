<?php
/**
 * add ip anonymize settings
 *
 * @author Clemens Rudolph
 * @created Mon, 27 Aug 2018 12:11:17 +0200
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
class Migration_20180827121117 extends Migration implements IMigration
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
        /*
         *$this->execute('
         *    CREATE TABLE IF NOT EXISTS `tgdprtimers`(
         *        `cTimerName` varchar(128) DEFAULT "" NOT NULL COMMENT "a preferably uniq name of the timer",
         *        `dTimerLastRun` datetime DEFAULT NULL COMMENT "no 00:00:00 time possible; use NULL instead",
         *        PRIMARY KEY `TimerName` (`cTimerName`)
         *    )
         *    ENGINE=InnoDB
         *    DEFAULT CHARSET=utf8
         *');
         */

        // setting up the cron-job in the cron-table
        $oCronDataProtection = $this->fetchArray('SELECT * FROM `tcron` WHERE `cJobArt` = "dataprotection"');
        if (0 <= sizeof($oCronDataProtection)) {
            $this->execute('
                INSERT INTO `tcron`(`kKey`, `cKey`, `cJobArt`, `nAlleXStd`,`cTabelle`, `cName`, `dStart`, `dStartZeit`, `dLetzterStart`)
                    VALUES(50, "", "dataprotection", 24, "", "", now(), "00:00:00", now())
            ');
        }

        // remove the old "IPs speichern" settings (teinstellungenconf::kEinstellungenConf=335,1133)
        // (these settings makes no more sense now)
        $this->removeConfig('global_ips_speichern');
        $this->removeConfig('bestellabschluss_ip_speichern');


        // --TODO-- create our big anon-protocol-table ...
        // create the journal-table
        $this->execute('
            CREATE TABLE IF NOT EXISTS `tanondatajournal`(
                  `kAnonDatenHistory` int(11) NOT NULL AUTO_INCREMENT
                , `cTableSource` varchar(255) default "" comment "names the table in which the change took place"
                , `cReason` varchar(255) default "" comment "describes the reason for the previous change"
                , `cOldValue` text default "" comment "content, before the chenages are occured"
                , `dEventTime` datetime default null comment "time of the event"
                , PRIMARY KEY `kAnonDatenHistory`(`kAnonDatenHistory`)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8
        ');

    }

    public function down()
    {
        // remove the journal-table
        $this->execute('DROP TABLE `tanondatajournal`');

        // remove new settings
        $this->removeConfig('anonymize_ip_mask_v4');
        $this->removeConfig('anonymize_ip_mask_v6');

        // remove the cron-job from the cron-table
        $oCronDataProtection = $this->fetchArray('SELECT * FROM `tcron` WHERE `cJobArt` = "dataprotection"');
        $this->execute('DELETE FROM `tcron` WHERE `kCron` = "'.$oCronDataProtection[0]['kCron'].'"');

        // remove the GDPR-timer table
        /*
         *$this->execute('DROP TABLE `tgdprtimers`');
         */

        // restore the old "IPs speichern" settings (teinstellungenconf::kEinstellungenConf=335,1133)
        // (these settings makes no more sense now)
        $this->execute('
            INSERT INTO `teinstellungenconf` VALUES
                (335, 1, "IP-Adresse bei Bestellung mitspeichern", "Soll die IP-Adresse des Kunden in der Datenbank gespeichert werden, wenn er eine Bestellung abschliesst?", "bestellabschluss_ip_speichern", "selectbox", NULL, 554, 1, 0, "Y"),
                (1133, 1 ,"IPs speichern", "Sollen IPs von Benutzern bei z.b. Umfragen, Tags etc. als Floodschutz oder sonstigen Trackingm&ouml;glichkeiten gespeichert werden?" ,"global_ips_speichern" ,"selectbox", NULL, 552, 1, 0 , "Y")
        ');
        $this->execute('
            INSERT INTO `teinstellungenconfwerte` VALUE
                ("335","Ja","Y","1"),
                ("335","Nein","N","2"),
                ("1133","Ja","Y","1"),
                ("1133","Nein","N","2")
        ');

        // --NOTE--
        // settings for "save IP yes/no", the current way, makes no sens anymore
        /*
         *$this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf)
         *    VALUES("335","7","Variationsbilder Groß skalieren","Soll die IP-Adresse des Kunden in der Datenbank gespeichert werden, wenn er eine Bestellung abschliesst?","bilder_variationen_gross_skalieren","selectbox","","580","1","0","Y")');
         *$this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("335","Ja","Y","1")');
         *$this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("335","Nein","N","2")');
         *
         *$this->execute('INSERT INTO teinstellungenconf(kEinstellungenConf,kEinstellungenSektion,cName,cBeschreibung,cWertName,cInputTyp,cModulId,nSort,nStandardAnzeigen,nModul,cConf)
         *    VALUES("1133","1","IPs speichern","Sollen IPs von Benutzern bei z.b. Umfragen, Tags etc. als Floodschutz oder sonstigen Trackingm&ouml;glichkeiten gespeichert werden?","global_ips_speichern","selectbox","","570","1","0","Y")');
         *$this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1133","Ja","Y","1")');
         *$this->execute('INSERT INTO teinstellungenconfwerte(kEinstellungenConf,cName,cWert,nSort) VALUES("1133","Nein","N","2")');
         */
    }
}
