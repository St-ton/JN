<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Admin;

use Cron\JobHydrator;
use Cron\JobInterface;
use DB\DbInterface;
use DB\ReturnType;
use Mapper\JobTypeToJob;
use Psr\Log\LoggerInterface;

/**
 * Class Listing
 * @package Cron\Admin
 */
final class Listing
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Listing constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->logger = $logger;
    }

    /**
     * @param int $id
     * @return int
     */
    public function resetQueueEntry(int $id): int
    {
        return $this->db->update('tjobqueue', 'kJobQueue', $id, (object)['nInArbeit' => 0]);
    }

    /**
     * @return JobInterface[]
     */
    public function getJobs(): array
    {
        $jobs     = [];
        $all      = $this->db->query(
            'SELECT *
              FROM tcron
              JOIN tjobqueue
                ON tcron.kCron = tjobqueue.kCron',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $hydrator = new JobHydrator();
        foreach ($all as $cron) {
            $cron->kCron     = (int)$cron->kCron;
            $cron->kKey      = (int)$cron->kKey;
            $cron->nAlleXStd = (int)$cron->nAlleXStd;
            $cron->nInArbeit = (bool)$cron->nInArbeit;
            $mapper          = new JobTypeToJob();
            $class           = $mapper->map($cron->cJobArt);
            $job             = new $class($this->db, $this->logger, $hydrator);
            /** @var JobInterface $job */
//            \Shop::dbg($job, false, 'JOB:');
            $jobs[] = $job->hydrate($cron);
//            \Shop::dbg($job, true, 'JOB after hydration:');
        }

        return $jobs;
    }
}
