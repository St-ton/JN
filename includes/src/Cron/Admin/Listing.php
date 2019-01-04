<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Admin;

use Cron\JobHydrator;
use Cron\JobInterface;
use DB\DbInterface;
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
     * @return JobInterface[]
     */
    public function getJobs(): array
    {
        $jobs = [];
        $all      = $this->db->selectAll('tcron', [], []);
        $hydrator = new JobHydrator();
        foreach ($all as $cron) {
            $cron->kCron     = (int)$cron->kCron;
            $cron->kKey      = (int)$cron->kKey;
            $cron->nAlleXStd = (int)$cron->nAlleXStd;
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
