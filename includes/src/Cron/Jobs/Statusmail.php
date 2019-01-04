<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Jobs;

use Cron\Job;
use Cron\JobHydrator;
use Cron\JobInterface;
use Cron\QueueEntry;
use DB\DbInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Statusmail
 * @package Cron\Jobs
 */
class Statusmail extends Job
{
    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, JobHydrator $hydrator)
    {
        parent::__construct($db, $logger, $hydrator);
        if (\JOBQUEUE_LIMIT_M_STATUSEMAIL > 0) {
            $this->setLimit((int)\JOBQUEUE_LIMIT_M_STATUSEMAIL);
        }
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
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'mailTools.php';
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'smartyInclude.php';
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'statusemail_inc.php';

        $statusMail = new \Statusmail($this->db);
        $this->setFinished($statusMail->send($jobData));

        return $this;
    }
}
