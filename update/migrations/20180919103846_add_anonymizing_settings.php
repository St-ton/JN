<?php
/**
 * add anonymizing settings
 *
 * @author Clemens Rudolph
 * @created Wed, 19 Sep 2018 10:38:46 +0200
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
 * setLocalization    - add localization
 * removeLocalization - remove localization
 * setConfig          - add / update config property
 * removeConfig       - remove config property
 */
class Migration_20180919103846 extends Migration implements IMigration
{
    protected $author = 'Clemens Rudolph';
    protected $description = 'add anonymizing settings';

    public function up()
    {
        // remove the old "IPs speichern" settings (teinstellungenconf::kEinstellungenConf=335,1133)
        $this->removeConfig('global_ips_speichern');
        $this->removeConfig('bestellabschluss_ip_speichern');

        // setting up the cron-job in the cron-table
        $oCronDataProtection = $this->fetchArray('SELECT * FROM tcron WHERE cJobArt = "dataprotection"');
        if (0 <= sizeof($oCronDataProtection)) {
            $this->execute('
                INSERT INTO tcron(kKey, cKey, cJobArt, nAlleXStd,cTabelle, cName, dStart, dStartZeit, dLetzterStart)
                    VALUES(50, "", "dataprotection", 24, "", "", NOW(), "00:00:00", NOW())
            ');
        }

        // create the journal-table
        $this->execute('
            CREATE TABLE IF NOT EXISTS tanondatajournal(
                kAnonDatenHistory INT(11) NOT NULL AUTO_INCREMENT,
                cTableSource VARCHAR(255) DEFAULT "" COMMENT "names the table in which the change took place",
                cReason VARCHAR(255) default "" COMMENT "describes the reason for the previous change",
                kId INT(11) DEFAULT NULL COMMENT "the original key of the appropriate table",
                cOldValue TEXT COMMENT "content, before the chenages are occured",
                dEventTime DATETIME DEFAULT NULL COMMENT "time of the event",
                PRIMARY KEY kAnonDatenHistory(kAnonDatenHistory),
                KEY kId (kId)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8
        ');
    }

    public function down()
    {
        // remove the journal-table
        $this->execute('DROP TABLE tanondatajournal');

        // remove the cron-job from the cron-table
        $oCronDataProtection = $this->fetchArray('SELECT * FROM tcron WHERE cJobArt = "dataprotection"');
        $this->execute('DELETE FROM tcron WHERE kCron = "'.$oCronDataProtection[0]['kCron'].'"');


        // restore the old "IPs speichern" settings (teinstellungenconf::kEinstellungenConf=335,1133)
        $this->execute('
            INSERT INTO teinstellungenconf VALUES
                (335, 1, "IP-Adresse bei Bestellung mitspeichern", "Soll die IP-Adresse des Kunden in der Datenbank gespeichert werden, wenn er eine Bestellung abschliesst?", "bestellabschluss_ip_speichern", "selectbox", NULL, 554, 1, 0, "Y"),
                (1133, 1 ,"IPs speichern", "Sollen IPs von Benutzern bei z.b. Umfragen, Tags etc. als Floodschutz oder sonstigen Trackingm&ouml;glichkeiten gespeichert werden?" ,"global_ips_speichern" ,"selectbox", NULL, 552, 1, 0 , "Y")
        ');
        $this->execute('
            INSERT INTO teinstellungenconfwerte VALUE
                ("335","Ja","Y","1"),
                ("335","Nein","N","2"),
                ("1133","Ja","Y","1"),
                ("1133","Nein","N","2")
        ');
    }
}
