<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;

use DB\DbInterface;
use Mapper\JobTypeToJob;
use Psr\Log\LoggerInterface;

/**
 * Class JobFactory
 * @package Cron
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
        $class  = $mapper->map($data->cJobArt);
        $job    = new $class($this->db, $this->logger, new JobHydrator());
        /** @var JobInterface $job */
        $job->hydrate($data);

        return $job;
    }
}
