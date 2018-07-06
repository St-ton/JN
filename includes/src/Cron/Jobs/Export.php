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
 * Class Export
 * @package Cron\Jobs
 */
class Export extends Job
{
    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        parent::__construct($db, $logger);
        if (JOBQUEUE_LIMIT_M_EXPORTE > 0) {
            $this->setLimit(JOBQUEUE_LIMIT_M_EXPORTE);
        }
    }

    /**
     * @param QueueEntry $queueEntry
     * @return bool
     */
    public function updateExportformatQueueBearbeitet(QueueEntry $queueEntry): bool
    {
        if ($queueEntry->kJobQueue > 0) {
            $this->db->delete('texportformatqueuebearbeitet', 'kJobQueue', (int)$queueEntry->kJobQueue);

            $ins                   = new \stdClass();
            $ins->kJobQueue        = $queueEntry->kJobQueue;
            $ins->kExportformat    = $queueEntry->kKey;
            $ins->nLimitN          = $queueEntry->nLimitN;
            $ins->nLimitM          = $queueEntry->nLimitM;
            $ins->nInArbeit        = $queueEntry->nInArbeit;
            $ins->dStartZeit       = $queueEntry->dStartZeit->format('Y-m-d H:i');
            $ins->dZuletztGelaufen = $queueEntry->dZuletztGelaufen->format('Y-m-d H:i');

            $this->db->insert('texportformatqueuebearbeitet', $ins);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $ef = new \Exportformat($this->getForeignKeyID());
        $ef->setLogger($this->logger);
        $finished = $ef->startExport($queueEntry, false, false, true);
        $this->updateExportformatQueueBearbeitet($queueEntry);
        $this->setFinished($finished);

        return $this;
    }
}
