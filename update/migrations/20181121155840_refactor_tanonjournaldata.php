<?php
/**
 * refactor_tanonjournaldata
 *
 * @author Michael Hillmann
 * @created Wed, 21 Nov 2018 15:58:40 +0100
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
class Migration_20181121155840 extends Migration implements IMigration
{
    protected $author = 'Michael Hillmann';
    protected $description = 'refactor_tanonjournaldata';

    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tanondatajournal`');
        $this->execute("
            CREATE TABLE IF NOT EXISTS tanondatajournal(
                kAnonDatenHistory INT(11) NOT NULL AUTO_INCREMENT,
                cIssuer VARCHAR(255) DEFAULT '' COMMENT 'application(cron), user, admin, plugin',
                iIssuerId INT(11) DEFAULT NULL COMMENT 'id of the issuer (e.g Kkunde, kPlugin)',
                cAction VARCHAR(255) DEFAULT '',
                cMessage TEXT DEFAULT '',
                dEventTime DATETIME DEFAULT NULL,
                PRIMARY KEY kAnonDatenHistory(kAnonDatenHistory),
                KEY kIssuer(iIssuerId)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8
        ");
    }

    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tanondatajournal`');
        $this->execute("
            CREATE TABLE IF NOT EXISTS tanondatajournal(
                kAnonDatenHistory INT(11) NOT NULL AUTO_INCREMENT,
                cIssuer VARCHAR(255) DEFAULT '' COMMENT 'application(cron), user, admin',
                iIssuerId INT(11) DEFAULT NULL COMMENT 'id of the issuer (only for user or admin)',
                dEventTime DATETIME DEFAULT NULL COMMENT 'time of the event',
                PRIMARY KEY kAnonDatenHistory(kAnonDatenHistory),
                KEY kIssuer(iIssuerId)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8
        ");
    }
}