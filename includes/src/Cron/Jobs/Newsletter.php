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

/**
 * Class Newsletter
 * @package Cron\Jobs
 */
class Newsletter extends Job
{
    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        if (JOBQUEUE_LIMIT_M_NEWSLETTER > 0) {
            $this->setLimit(JOBQUEUE_LIMIT_M_NEWSLETTER);
        }
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);

        return $this;
    }
}
