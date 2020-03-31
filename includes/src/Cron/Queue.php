<?php declare(strict_types=1);

namespace JTL\Cron;

use DateTime;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class Queue
 * @package JTL\Cron
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
     * @param LoggerInterface $logger
     * @param JobFactory      $factory
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, JobFactory $factory)
    {
        $this->db      = $db;
        $this->logger  = $logger;
        $this->factory = $factory;
    }

    /**
     * @return QueueEntry[]
     */
    public function loadQueueFromDB(): array
    {
        $queueData = $this->db->query(
            'SELECT *
                FROM tjobqueue
                WHERE isRunning = 0
                    AND startTime <= NOW()',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($queueData as $entry) {
            $this->queueEntries[] = new QueueEntry($entry);
        }
        $this->logger->debug('Loaded ' . \count($this->queueEntries) . ' existing job(s).');

        return $this->queueEntries;
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
     * @throws \Exception
     */
    public function run(Checker $checker): void
    {
        if ($checker->isLocked()) {
            $this->logger->debug('Cron currently locked');
            exit;
        }
        $checker->lock();
        $this->enqueueCronJobs($checker->check());
        $this->loadQueueFromDB();
        foreach ($this->queueEntries as $i => $queueEntry) {
            if ($i >= \JOBQUEUE_LIMIT_JOBS) {
                $this->logger->debug('Job limit reached after ' . \JOBQUEUE_LIMIT_JOBS . ' jobs.');
                break;
            }
            $job                       = $this->factory->create($queueEntry);
            $queueEntry->tasksExecuted = $job->getExecuted();
            $queueEntry->taskLimit     = $job->getLimit();
            $queueEntry->isRunning     = 1;
            $this->logger->notice('Got job (ID = ' . $job->getCronID() . ', type = ' . $job->getType() . ')');
            $job->start($queueEntry);
            $queueEntry->isRunning = 0;
            $queueEntry->lastStart = new DateTime();
            $this->db->update(
                'tcron',
                'cronID',
                $job->getCronID(),
                (object)['lastFinish' => $queueEntry->lastFinish->format('Y-m-d H:i')]
            );
            $job->saveProgress($queueEntry);
            if ($job->isFinished()) {
                $this->logger->notice('Job ' . $job->getID() . ' successfully finished.');
                $job->delete();
            }
            \executeHook(\HOOK_JOBQUEUE_INC_BEHIND_SWITCH, [
                'oJobQueue' => $queueEntry,
                'job'       => $job
            ]);
        }
        $checker->unlock();
    }
}
