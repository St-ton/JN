<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;

use DB\DbInterface;
use DB\ReturnType;
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
        $this->db     = $db;
        $this->logger = $logger;
    }

    /**
     * @return \stdClass[]
     */
    public function check(): array
    {
        $jobs = $this->db->query(
            'SELECT tcron.*
                FROM tcron
                LEFT JOIN tjobqueue 
                    ON tjobqueue.kCron = tcron.kCron
                WHERE (tcron.dLetzterStart IS NULL 
                    OR (NOW() > ADDDATE(tcron.dLetzterStart, INTERVAL tcron.nAlleXStd HOUR)))
                    AND tcron.dStart < NOW()
                    AND tjobqueue.kJobQueue IS NULL',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $this->logger->debug('Found ' . \count($jobs) . ' new cron jobs.');

        return $jobs;
    }
}
