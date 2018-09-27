<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Jobs;


use Cron\Job;
use Cron\JobInterface;
use Cron\QueueEntry;
use DB\DbInterface;
use Psr\Log\LoggerInterface;

/**
 * Class GeneralDataProtect
 * @package Cron\Jobs
 */
class GeneralDataProtect extends Job
{

    private $oLogger = null;

    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        parent::__construct($db, $logger);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        \Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = \Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--

        //$this->oLogger->debug('cronjob "includes/src/Cron/Jobs/GeneralDataProtect.php": '); // --DEBUG--
    }


    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        //parent::start($queueEntry); // needed ?

        //$this->oLogger->debug('job: '.print_r($this, true)); // --DEBUG--
        $this->oLogger->debug('last started: - - - - - - - - - - - - - - - - - - - - '
            .print_r($this->getDateLastStarted()->format('Y.m.d H:i:s'),true)); // --DEBUG--

        $oTableCleaner = new \GeneralDataProtection\TableCleaner();
        $oTableCleaner->execute();

        return $this;
    }

}
