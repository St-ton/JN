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
//use GeneralDataProtection;

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
    }


    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        //parent::start($queueEntry); // needed ?

        //
        $oGdprRunner = new \GeneralDataProtection\GdprRunner();
        $oGdprRunner->execute();

        return $this;
    }

}
