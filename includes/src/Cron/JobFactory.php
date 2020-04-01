<?php declare(strict_types=1);

namespace JTL\Cron;

use InvalidArgumentException;
use JTL\Cron\Job\Dummy;
use JTL\DB\DbInterface;
use JTL\Mapper\JobTypeToJob;
use Psr\Log\LoggerInterface;

/**
 * Class JobFactory
 * @package JTL\Cron
 */
class JobFactory
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * JobFactory constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->logger = $logger;
    }

    /**
     * @param QueueEntry $data
     * @return JobInterface
     */
    public function create(QueueEntry $data): JobInterface
    {
        $mapper = new JobTypeToJob();
        try {
            $class = $mapper->map($data->jobType);
        } catch (InvalidArgumentException $e) {
            $class = Dummy::class;
        }
        $job = new $class($this->db, $this->logger, new JobHydrator());
        /** @var JobInterface $job */
        $job->hydrate($data);

        return $job;
    }
}
