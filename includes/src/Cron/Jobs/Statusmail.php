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
use DB\ReturnType;
use function Functional\map;
use Psr\Log\LoggerInterface;

require_once PFAD_ROOT . \PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . \PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'statusemail_inc.php';

/**
 * Class Statusmail
 * @package Cron\Jobs
 */
class Statusmail extends Job
{
    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        parent::__construct($db, $logger);
        if (\JOBQUEUE_LIMIT_M_STATUSEMAIL > 0) {
            $this->setLimit(\JOBQUEUE_LIMIT_M_STATUSEMAIL);
        }
    }

    /**
     * @param string $dateStart
     * @param string $interval - one of 'hour', 'day', 'week', 'month', 'year'
     * @return bool
     */
    public function isIntervalExceeded($dateStart, $interval): bool
    {
        if ($dateStart === '0000-00-00 00:00:00') {
            return true;
        }
        $oStartTime = \date_create($dateStart);

        if ($oStartTime === false) {
            return false;
        }

        $oEndTime = $oStartTime->modify('+1 ' . $interval);
        if ($oEndTime === false) {
            return false;
        }

        return \date_create()->format('YmdHis') >= $oEndTime->format('YmdHis');
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $jobData = $this->getJobData();
        if ($jobData === null) {
            return $this;
        }
        $statusMail = new \Statusmail($this->db);
        $this->setFinished($statusMail->send($jobData));

        return $this;
    }
}
