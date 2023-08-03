<?php declare(strict_types=1);

/**
 * create mailqueue tables
 *
 * @author sl
 * @created Thu, 26 Jan 2023 15:01:45 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20230126150145
 */
class Migration_20230126150145 extends Migration implements IMigration
{
    protected $author = 'sl';
    protected $description = 'create mailqueue tables and cronjob';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("CREATE TABLE `emails` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `reSend` tinyint(1) unsigned NOT NULL DEFAULT 0,
              `isSendingNow` tinyint(1) unsigned DEFAULT 0,
              `sendCount` int(11) unsigned DEFAULT 0,
              `errorCount` int(11) DEFAULT 0,
              `lastError` text DEFAULT NULL,
              `dateQueued` datetime DEFAULT NULL,
              `dateSent` datetime DEFAULT NULL,
              `fromMail` varchar(255) DEFAULT NULL,
              `fromName` varchar(255) DEFAULT NULL,
              `toMail` varchar(255) DEFAULT NULL,
              `toName` varchar(255) DEFAULT NULL,
              `replyToMail` varchar(255) DEFAULT NULL,
              `replyToName` varchar(255) DEFAULT NULL,
              `subject` tinytext DEFAULT NULL,
              `bodyHTML` longtext DEFAULT NULL,
              `bodyText` longtext DEFAULT NULL,
              `hasAttachments` tinyint(1) unsigned DEFAULT 0,
              `copyRecipients` text DEFAULT NULL,
              `templateId` varchar(255) DEFAULT NULL,  
              `languageId` int (11) unsigned NOT NULL, 
              `customerGroupID` int (11) unsigned NOT NULL, 
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        $this->execute("CREATE TABLE `email_attachments` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `mailID` int(11) unsigned NOT NULL,
              `mime` varchar(100) NOT NULL DEFAULT 'application/pdf',
              `dir` varchar(255) NOT NULL,
              `fileName` varchar(255) NOT NULL,
              `name` varchar(255) DEFAULT NULL,
              `encoding` varchar(45) NOT NULL DEFAULT 'base64',
              PRIMARY KEY (`id`),
              KEY `mailID_FK_idx` (`mailID`),
              CONSTRAINT `mailID_FK` FOREIGN KEY (`mailID`) REFERENCES `emails` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

        $cron            = new stdClass();
        $cron->name      = 'sendmail';
        $cron->jobType   = 'sendMailQueue';
        $cron->frequency = 0;
        $cron->startDate = '2023-01-01 00:00:00';
        $cron->startTime = '00:00';
        $cron->nextStart = '2023-01-01 00:00:00';

        $cronID = $this->getDB()->insertRow('tcron', $cron);


        $jobQueue = new stdClass();
        $jobQueue->cronID    = $cronID;
        $jobQueue->jobType   = 'sendMailQueue';
        $jobQueue->isRunning = 0;
        $jobQueue->startTime = '2023-01-01 00:00:00';
        $jobQueue->lastStart = '2023-01-01 00:00:00';

        $this->getDB()->insertRow('tjobqueue', $jobQueue);
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `email_attachments`');
        $this->execute('DROP TABLE IF EXISTS `emails`');
        $this->execute("DELETE FROM tjobqueue WHERE jobType = 'sendMailQueue'");
        $this->execute("DELETE FROM tcron WHERE jobType = 'sendMailQueue'");
    }
}
