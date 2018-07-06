<?php
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
        // @todo: catch Exception
        $class = $mapper->map($data->cJobArt);
        $job   = new $class($this->db, $this->logger);
        /** @var JobInterface $job */
        $job->setType($data->cJobArt);
        $job->setTable($data->cTabelle);
        $job->setForeignKey($data->cKey);
        $job->setForeignKeyID((int)$data->kKey);
        $job->setCronID((int)$data->kCron);
        // @todo: setID vs. setCrontID
        $job->setID((int)$data->kCron);
        $job->setQueueID((int)$data->kJobQueue);
        if ($data->nLimitM > 0) {
            $job->setLimit((int)$data->nLimitM);
        }
        if ($data->nLimitN > 0) {
            $job->setExecuted((int)$data->nLimitN);
        }

        return $job;
    }
}
