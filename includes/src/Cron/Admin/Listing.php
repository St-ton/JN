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
        return $this->db->update('tjobqueue', 'kJobQueue', $id, (object)['nInArbeit' => 0]);
    }

    /**
     * @param int $id
     * @return int
     */
    public function deleteQueueEntry(int $id): int
    {
        $affected = $this->db->queryPrepared(
            'DELETE FROM tjobqueue WHERE kCron = :id',
            ['id' => $id],
            ReturnType::AFFECTED_ROWS
        );

        return $affected + $this->db->queryPrepared(
            'DELETE FROM tcron WHERE kCron = :id',
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
        $date            = new \DateTime($post['date']);
        $ins             = new \stdClass();
        $ins->nAlleXStd  = (int)$post['frequency'];
        $ins->cKey       = '';
        $ins->cTabelle   = '';
        $ins->kKey       = 999;
        $ins->cJobArt    = $post['type'];
        $ins->cName      = 'manuell@' . \date('Y-m-d H:i:s');
        $ins->dStartZeit = \strlen($post['time']) === 5 ? $post['time'] . ':00' : $post['time'];
        $ins->dStart     = $date->format('Y-m-d H:i:s');

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
            'SELECT tcron.*, tjobqueue.nInArbeit, tjobqueue.kJobQueue
                FROM tcron
                LEFT JOIN tjobqueue
                    ON tcron.kCron = tjobqueue.kCron',
            ReturnType::ARRAY_OF_OBJECTS
        );
//        \Shop::dbg($all);
        foreach ($all as $cron) {
            $cron->kJobQueue = (int)($cron->kJobQueue ?? 0);
            $cron->kCron     = (int)$cron->kCron;
            $cron->kKey      = (int)$cron->kKey;
            $cron->nAlleXStd = (int)$cron->nAlleXStd;
            $cron->nInArbeit = (bool)$cron->nInArbeit;
            $mapper          = new JobTypeToJob();
            $class           = $mapper->map($cron->cJobArt);
            $job             = new $class($this->db, $this->logger, $this->hydrator);
            /** @var JobInterface $job */
            $jobs[] = $job->hydrate($cron);
        }

        return $jobs;
    }
}
