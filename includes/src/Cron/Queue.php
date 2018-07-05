<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;


use DB\DbInterface;

/**
 * Class Queue
 * @package Cron
 */
class Queue
{
    /**
     * @var JobInterface[]
     */
    private $jobs = [];

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Queue constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return JobInterface[]
     */
    public function getJobs(): array
    {
        return $this->jobs;
    }

    /**
     * @param JobInterface[] $jobs
     */
    public function setJobs(array $jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * @param JobInterface[] $jobs
     */
    public function addJobs(array $jobs)
    {
        $this->jobs = array_merge($this->jobs, $jobs);
    }

    public function loadJobsFromDB()
    {
        $queueData = $this->db->query(
            'SELECT * 
                FROM tjobqueue 
                WHERE nInArbeit = 0 
                    AND dStartZeit < now()',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $factory   = new JobFactory();
        foreach ($queueData as $entry) {
            $job = $factory->create($entry);
            // @todo catch Exception
            $this->jobs[] = $job;
        }
    }

    /**
     * @return int
     */
    public function saveToDatabase(): int
    {
        $affected = 0;
        foreach ($this->jobs as $job) {
            $queueEntry             = new \stdClass();
            $queueEntry->kCron      = $job->getID();
            $queueEntry->kKey       = $job->getForeignKeyID();
            $queueEntry->cKey       = $job->getForeignKey();
            $queueEntry->cTabelle   = $job->getTable();
            $queueEntry->cJobArt    = $job->getType();
            $queueEntry->dStartZeit = $job->getDateLastStarted()->format('Y-m-d H:i');
            $queueEntry->nLimitN    = 0;
            $queueEntry->nLimitM    = $job->getLimit();
            $queueEntry->nInArbeit  = 0;
            $affected               += $this->db->insert('tjobqueue', $queueEntry);
        }

        return $affected;
    }

    public function runJobs()
    {
        foreach ($this->jobs as $i => $job) {
            if ($i >= JOBQUEUE_LIMIT_JOBS) {
                break;
            }
            $queueEntry                 = new QueueEntry($job);
            $queueEntry->nLastArticleID = $job->nLastArticleID ?? 0;
            $queueEntry->nInArbeit      = 1;
            \Jtllog::cronLog('Got job - ' . $job->getID() . ', type = ' . $job->getType() . ')');
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
            }
            executeHook(HOOK_JOBQUEUE_INC_BEHIND_SWITCH, [
                'oJobQueue' => $queueEntry,
                'job'       => $job
            ]);
        }
    }
}
