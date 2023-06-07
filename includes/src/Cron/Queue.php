<?php declare(strict_types=1);

namespace JTL\Cron;

use DateTime;
use JTL\DB\DbInterface;
use JTL\Shop;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class Queue
 * @package JTL\Cron
 */
class Queue
{
    /**
     * Queue constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     * @param JobFactory      $factory
     */
    public function __construct(private DbInterface $db, private LoggerInterface $logger, private JobFactory $factory)
    {
        Shop::Container()->getGetText()->loadAdminLocale('pages/cron');
    }

    /**
     * @return QueueEntry[]
     */
    public function loadQueueFromDB(): array
    {
        $queueEntries = $this->db->getCollection(
            'SELECT tjobqueue.*, tcron.nextStart, tcron.startTime AS cronStartTime, tcron.frequency
                FROM tjobqueue
                JOIN tcron
                    ON tcron.cronID = tjobqueue.cronID
                WHERE tjobqueue.isRunning = 0
                    AND tjobqueue.startTime <= NOW()'
        )->map(static function ($e): QueueEntry {
            return new QueueEntry($e);
        })->toArray();
        $this->logger->debug(\sprintf('Loaded %d existing job(s).', \count($queueEntries)));

        return $queueEntries;
    }

    /**
     * @return int
     */
    public function unStuckQueues(): int
    {
        return $this->db->getAffectedRows(
            'UPDATE tjobqueue
                SET isRunning = 0
                WHERE isRunning = 1
                    AND startTime <= NOW()
                    AND lastStart IS NOT NULL
                    AND DATE_SUB(CURTIME(), INTERVAL :ntrvl Hour) > lastStart',
            ['ntrvl' => \QUEUE_MAX_STUCK_HOURS]
        );
    }

    /**
     * @param stdClass[] $jobs
     */
    public function enqueueCronJobs(array $jobs): void
    {
        foreach ($jobs as $job) {
            $queueEntry                = new stdClass();
            $queueEntry->cronID        = $job->cronID;
            $queueEntry->foreignKeyID  = $job->foreignKeyID ?? '_DBNULL_';
            $queueEntry->foreignKey    = $job->foreignKey ?? '_DBNULL_';
            $queueEntry->tableName     = $job->tableName;
            $queueEntry->jobType       = $job->jobType;
            $queueEntry->startTime     = 'NOW()';
            $queueEntry->taskLimit     = 0;
            $queueEntry->tasksExecuted = 0;
            $queueEntry->isRunning     = 0;

            $this->db->insert('tjobqueue', $queueEntry);
        }
    }

    /**
     * @param Checker $checker
     * @return int
     * @throws \Exception
     */
    public function run(Checker $checker): int
    {
        if ($checker->isLocked()) {
            $this->logger->debug('Cron currently locked');

            return -1;
        }
        $checker->lock();
        $this->enqueueCronJobs($checker->check());
        $affected = $this->unStuckQueues();
        if ($affected > 0) {
            $this->logger->debug(\sprintf('Unstuck %d job(s).', $affected));
        }
        $queueEntries = $this->loadQueueFromDB();
        \shuffle($queueEntries);
        foreach ($queueEntries as $i => $queueEntry) {
            if ($i >= \JOBQUEUE_LIMIT_JOBS) {
                $this->logger->debug(\sprintf('Job limit reached after %d jobs.', \JOBQUEUE_LIMIT_JOBS));
                break;
            }
            $job                       = $this->factory->create($queueEntry);
            $queueEntry->tasksExecuted = $job->getExecuted();
            $queueEntry->taskLimit     = $job->getLimit();
            $queueEntry->isRunning     = 1;
            $this->logger->notice('Got job ' . \get_class($job)
                . ' (ID = ' . $job->getCronID()
                . ', type = ' . $job->getType()
                . ', frequency = ' . $job->getFrequency() . ')');
            $job->start($queueEntry);

            $queueEntry->isRunning = 0;
            $queueEntry->lastStart = new DateTime();

            $st        = $queueEntry->cronStartTime;
            $now       = new DateTime();
            $nextStart = new DateTime();
            $nextStart->setTime((int)$st->format('H'), (int)$st->format('i'), (int)$st->format('s'));
            while ($nextStart <= $now) {
                $nextStart->modify('+' . $job->getFrequency() . ' hours');
            }
            $this->db->update(
                'tcron',
                'cronID',
                $job->getCronID(),
                (object)[
                    'nextStart'  => $nextStart->format('Y-m-d H:i:s'),
                    'lastFinish' => $queueEntry->lastFinish->format('Y-m-d H:i')
                ]
            );
            \executeHook(\HOOK_JOBQUEUE_INC_BEHIND_SWITCH, [
                'oJobQueue' => $queueEntry,
                'job'       => $job,
                'logger'    => $this->logger
            ]);
            $job->saveProgress($queueEntry);
            if ($job->isFinished()) {
                $this->logger->notice('Job ' . $job->getID() . ' successfully finished.');
                $job->delete();
            }
        }
        $checker->unlock();

        return \count($queueEntries);
    }
}
