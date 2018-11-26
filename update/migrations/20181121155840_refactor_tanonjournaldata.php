<?php
/**
 * refactor_tanonjournaldata
 *
 * @author mh
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
    protected $author = 'mh';
    protected $description = 'Refactor tanonjournaldata';

    public function up()
    {
        $this->execute('DROP TABLE IF EXISTS `tanondatajournal`');
        $this->execute("
            CREATE TABLE IF NOT EXISTS tanondatajournal(
                kAnondatajournal INT(11) NOT NULL AUTO_INCREMENT,
                cIssuer VARCHAR(255) DEFAULT '' COMMENT 'application(cron), user, admin, plugin',
                iIssuerId INT(11) DEFAULT NULL COMMENT 'id of the issuer (e.g Kkunde, kPlugin)',
                cAction VARCHAR(255) DEFAULT '',
                cDetail TEXT DEFAULT '' COMMENT 'json with important data',
                cMessage TEXT DEFAULT '' COMMENT 'more detailed description of the action',
                dEventTime DATETIME DEFAULT NULL,
                PRIMARY KEY kAnondatajournal(kAnondatajournal),
                KEY kIssuer(iIssuerId)
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8
        ");

        $this->setLocalization('ger', 'account data', 'customerOpenOrders', 'Sie haben noch %d offene Bestellungen%s. Wenn Sie Ihr Kundenkonto jetzt löschen, werden alle 
        restlichen Daten automatisch gelöscht, sobald alle Bestellungen abgeschlossen sind.');
        $this->setLocalization('eng', 'account data', 'customerOpenOrders', 'You have %d open orders%s.
        You can delete your account. The remaining data will be deleted automatically after all orders are finished.');
        $this->setLocalization('ger', 'account data', 'customerOrdersInCancellationTime', ' und %d Bestellungen deren Retourenfrist noch nicht abgelaufen ist');
        $this->setLocalization('eng', 'account data', 'customerOrdersInCancellationTime', ' and %d orders in cancellation time');

        $this->setConfig(
            'global_cancellation_time',
            14,
            CONF_GLOBAL,
            'Retourenfrist',
            'number',
            650,
            (object)['cBeschreibung' => 'Retourenfrist für den gesamten Shop.']
        );
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

        $this->removeLocalization('customerOpenOrders');
        $this->removeLocalization('customerOrdersInCancellationTime');

        $this->removeConfig('global_cancellation_time');
    }
}
