<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Admin;

use Cron\JobHydrator;
use Cron\JobInterface;
use Cron\Type;
use DB\DbInterface;
use DB\ReturnType;
use Events\Dispatcher;
use Events\Event;
use Mapper\JobTypeToJob;
use Psr\Log\LoggerInterface;

/**
 * Class Controller
 * @package Cron\Admin
 */
final class Controller
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
     * @var JobHydrator
     */
    private $hydrator;

    /**
     * Listing constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     * @param JobHydrator     $hydrator
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, JobHydrator $hydrator)
    {
        $this->db       = $db;
        $this->logger   = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * @param int $id
     * @return int
     */
    public function resetQueueEntry(int $id): int
    {
        return $this->db->update('tjobqueue', 'id', $id, (object)['isRunning' => 0]);
    }

    /**
     * @param int $id
     * @return int
     */
    public function deleteQueueEntry(int $id): int
    {
        $affected = $this->db->queryPrepared(
            'DELETE FROM tjobqueue WHERE cronID = :id',
            ['id' => $id],
            ReturnType::AFFECTED_ROWS
        );

        return $affected + $this->db->queryPrepared(
            'DELETE FROM tcron WHERE cronID = :id',
            ['id' => $id],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param array $post
     * @return int
     */
    public function addQueueEntry(array $post): int
    {
        $mapper = new JobTypeToJob();
        try {
            $mapper->map($post['type']);
        } catch (\InvalidArgumentException $e) {
            return -1;
        }
        $date           = new \DateTime($post['date']);
        $ins            = new \stdClass();
        $ins->frequency = (int)$post['frequency'];
        $ins->jobType   = $post['type'];
        $ins->name      = 'manuell@' . \date('Y-m-d H:i:s');
        $ins->startTime = \strlen($post['time']) === 5 ? $post['time'] . ':00' : $post['time'];
        $ins->startDate = $date->format('Y-m-d H:i:s');

        return $this->db->insert('tcron', $ins);
    }

    /**
     * @return string[]
     */
    public function getAvailableCronJobs(): array
    {
        $available = [
            Type::IMAGECACHE,
            Type::STATUSMAIL,
            Type::NEWSLETTER,
            Type::DATAPROTECTION,
        ];
        Dispatcher::getInstance()->fire(Event::GET_AVAILABLE_CRONJOBS, ['jobs' => &$available]);

        return $available;
    }

    /**
     * @return JobInterface[]
     */
    public function getJobs(): array
    {
        $jobs = [];
        $all  = $this->db->query(
            'SELECT tcron.*, tjobqueue.isRunning, tjobqueue.jobQueueID
                FROM tcron
                LEFT JOIN tjobqueue
                    ON tcron.cronID = tjobqueue.cronID',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($all as $cron) {
            $cron->jobQueueID = (int)($cron->jobQueueID ?? 0);
            $cron->cronID     = (int)$cron->cronID;
            if ($cron->foreignKeyID !== null) {
                $cron->foreignKeyID = (int)$cron->foreignKeyID;
            }
            $cron->frequency = (int)$cron->frequency;
            $cron->isRunning = (bool)$cron->isRunning;
            $mapper          = new JobTypeToJob();
            try {
                $class = $mapper->map($cron->jobType);
                $job   = new $class($this->db, $this->logger, $this->hydrator);
                /** @var JobInterface $job */
                $jobs[] = $job->hydrate($cron);
            } catch (\InvalidArgumentException $e) {
                $this->logger->info('Invalid cron job found: ' . $cron->jobType);
            }
        }

        return $jobs;
    }
}
