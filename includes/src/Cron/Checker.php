<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use Psr\Log\LoggerInterface;

/**
 * Class Checker
 * @package JTL\Cron
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
                    ON tjobqueue.cronID = tcron.cronID
                WHERE (tcron.lastStart IS NULL 
                    OR (NOW() > ADDDATE(tcron.lastStart, INTERVAL tcron.frequency HOUR)))
                    AND tcron.startDate < NOW()
                    AND tjobqueue.jobQueueID IS NULL',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $this->logger->debug('Found ' . \count($jobs) . ' new cron jobs.');

        return $jobs;
    }
}
