<?php declare(strict_types=1);

namespace JTL\Cron;

use JTL\DB\DbInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Checker
 * @package JTL\Cron
 */
class Checker
{
    /**
     * @var resource|bool
     */
    private $filePointer;

    /**
     * Checker constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(private readonly DbInterface $db, private readonly LoggerInterface $logger)
    {
        if (!\file_exists(\JOBQUEUE_LOCKFILE)) {
            \touch(\JOBQUEUE_LOCKFILE);
        }
        $this->filePointer = \fopen(\JOBQUEUE_LOCKFILE, 'rb');
    }

    public function __destruct()
    {
        \fclose($this->filePointer);
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        if ($this->filePointer === false || $this->lock()) {
            return false;
        }
        $this->unlock();

        return true;
    }

    /**
     * @return bool
     */
    public function lock(): bool
    {
        return \flock($this->filePointer, \LOCK_EX | \LOCK_NB);
    }

    /**
     * @return bool
     */
    public function unlock(): bool
    {
        return \flock($this->filePointer, \LOCK_UN);
    }

    /**
     * @return \stdClass[]
     */
    public function check(): array
    {
        $jobs = $this->db->getObjects(
            'SELECT tcron.*
                FROM tcron
                LEFT JOIN tjobqueue 
                    ON tjobqueue.cronID = tcron.cronID
                WHERE (tcron.lastStart IS NULL
                           OR tcron.nextStart IS NULL
                           OR tcron.nextStart < NOW())
                    AND tcron.startDate < NOW()
                    AND tjobqueue.jobQueueID IS NULL'
        );
        $this->logger->debug('Found {cnt} new cron jobs.', ['cnt' => \count($jobs)]);

        return $jobs;
    }
}
