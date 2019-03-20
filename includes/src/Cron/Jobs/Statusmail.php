<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Jobs;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;

/**
 * Class Statusmail
 * @package JTL\Cron\Jobs
 */
class Statusmail extends Job
{
    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);
        if (\JOBQUEUE_LIMIT_M_STATUSEMAIL > 0) {
            $this->setLimit((int)\JOBQUEUE_LIMIT_M_STATUSEMAIL);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $jobData = $this->getJobData();
        if ($jobData === null) {
            $this->setFinished(true);

            return $this;
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'mailTools.php';
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'smartyInclude.php';
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'statusemail_inc.php';

        $statusMail = new \JTL\Statusmail($this->db);
        $this->setFinished($statusMail->send($jobData));

        return $this;
    }
}
