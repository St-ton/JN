<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;


use DB\DbInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Queue
 * @package Cron
 */
class Queue
{
    /**
     * @var QueueEntry[]
     */
    private $queueEntries = [];

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JobFactory
     */
    private $factory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Queue constructor.
     * @param DbInterface     $db
     * @param JobFactory      $factory
     * @param LoggerInterface $logger
     */
    public function __construct(DbInterface $db, JobFactory $factory, LoggerInterface $logger)
    {
        $this->db      = $db;
        $this->factory = $factory;
        $this->logger  = $logger;
    }

    public function loadQueueFromDB()
    {
        $queueData = $this->db->query(
            'SELECT * 
                FROM tjobqueue 
                WHERE nInArbeit = 0 
                    AND dStartZeit < now()',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($queueData as $entry) {
            $this->queueEntries[] = new QueueEntry($entry);
        }
    }

    /**
     * @param array $jobs
     */
    public function enqueueCronJobs(array $jobs)
    {
        foreach ($jobs as $job) {
            $queueEntry             = new \stdClass();
            $queueEntry->kCron      = $job->kCron;
            $queueEntry->kKey       = $job->kKey;
            $queueEntry->cKey       = $job->cKey;
            $queueEntry->cTabelle   = $job->cTabelle;
            $queueEntry->cJobArt    = $job->cJobArt;
            $queueEntry->dStartZeit = $job->dStartZeit;
            $queueEntry->nLimitN    = 0;
            $queueEntry->nLimitM    = 0;
            $queueEntry->nInArbeit  = 0;

            $this->db->insert('tjobqueue', $queueEntry);
        }
    }

    public function run()
    {
        foreach ($this->queueEntries as $i => $queueEntry) {
            if ($i >= JOBQUEUE_LIMIT_JOBS) {
                break;
            }
            $job                   = $this->factory->create($queueEntry);
            $queueEntry->nLimitM   = $job->getLimit();
            $queueEntry->nInArbeit = 1;
            $this->logger->log(JTLLOG_LEVEL_NOTICE, 'Got job - ' . $job->getID() . ', type = ' . $job->getType() . ')');
            $job->start($queueEntry);
            $queueEntry->nInArbeit        = 0;
            $queueEntry->dZuletztGelaufen = new \DateTime();
            $this->db->update(
                'tcron',
                'kCron',
                $job->getCronID(),
                (object)['dLetzterStart' => $queueEntry->dZuletztGelaufen->format('Y-m-d H:i')]
            );
            if ($job->isFinished()) {
                $this->db->delete('tjobqueue', 'kCron', $job->getCronID());
            } else {
                $update                   = new \stdClass();
                $update->dZuletztgelaufen = 'now';
                $update->nLimitN          = $queueEntry->nLimitN;
                $update->nlimitM          = $queueEntry->nLimitM;
                $update->nLastArticleID   = $queueEntry->nLastArticleID;
                $this->db->update('tjobqueue', 'kCron', $job->getCronID(), $update);
            }
            executeHook(HOOK_JOBQUEUE_INC_BEHIND_SWITCH, [
                'oJobQueue' => $queueEntry,
                'job'       => $job
            ]);
        }
    }
}
