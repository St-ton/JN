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
 * Class Export
 * @package Cron\Jobs
 */
class Export extends Job
{
    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db)
    {
        parent::__construct($db);
        if (JOBQUEUE_LIMIT_M_EXPORTE > 0) {
            $this->setLimit(JOBQUEUE_LIMIT_M_EXPORTE);
        }
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $ef       = new \Exportformat($this->getForeignKeyID());
        $finished = $ef->startExport($queueEntry, false, false, true);
        $this->setFinished($finished);

        return $this;
    }
}
