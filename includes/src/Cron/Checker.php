<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;


use DB\DbInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Checker
 * @package Cron
 */
class Checker
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
     * @var JobFactory
     */
    private $factory;

    /**
     * Checker constructor.
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
     * @return array
     */
    public function check(): array
    {
        $jobs     = [];
        $cronData = $this->db->query(
            "SELECT tcron.*
                FROM tcron
                LEFT JOIN tjobqueue 
                    ON tjobqueue.kCron = tcron.kCron
                WHERE ((tcron.dLetzterStart = '0000-00-00 00:00:00' OR tcron.dLetzterStart = '1970-01-01 00:00:00') 
                    OR (UNIX_TIMESTAMP(now()) > (UNIX_TIMESTAMP(tcron.dLetzterStart) + (3600 * tcron.nAlleXStd))))
                    AND tcron.dStart < now()
                    AND tjobqueue.kJobQueue IS NULL",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (count($cronData) === 0) {
            $this->logger->log(JTLLOG_LEVEL_DEBUG, 'No cron jobs found');

            return $jobs;
        }

        foreach ($cronData as $item) {
            $job = $this->factory->create($item);
            $this->logger->log(JTLLOG_LEVEL_DEBUG, 'Starting cron ' . $job->getID());

            executeHook(HOOK_CRON_INC_SWITCH, [
                'job'     => $job,
                'nLimitM' => $job->getLimit()
            ]);
            $jobs[] = $job;
        }

        return $jobs;
    }
}
