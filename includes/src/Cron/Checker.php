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
     * Checker constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        $this->db      = $db;
        $this->logger  = $logger;
    }

    /**
     * @return array
     */
    public function check(): array
    {
        $jobs = $this->db->query(
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
        $this->logger->debug('Found ' . \count($jobs) . ' new cron jobs.');

        return $jobs;
    }
}
