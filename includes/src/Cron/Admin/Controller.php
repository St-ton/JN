<?php declare(strict_types=1);

namespace JTL\Cron\Admin;

use DateTime;
use InvalidArgumentException;
use JTL\Cache\JTLCacheInterface;
use JTL\Cron\Job\Statusmail;
use JTL\Cron\JobHydrator;
use JTL\Cron\JobInterface;
use JTL\Cron\Type;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Mapper\JobTypeToJob;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class Controller
 * @package JTL\Cron\Admin
 * @deprecated since 5.2.0
 */
final class Controller
{
    /**
     * Controller constructor.
     * @param DbInterface       $db
     * @param LoggerInterface   $logger
     * @param JobHydrator       $hydrator
     * @param JTLCacheInterface $cache
     */
    public function __construct(
        private DbInterface $db,
        private LoggerInterface $logger,
        private JobHydrator $hydrator,
        private JTLCacheInterface $cache
    ) {
    }

    /**
     * @param int $jobQueueId
     * @return int
     * @deprecated since 5.2.0
     */
    public function resetQueueEntry(int $jobQueueId): int
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
        return $this->db->update('tjobqueue', 'jobQueueID', $jobQueueId, (object)['isRunning' => 0]);
    }

    /**
     * @param int $cronId
     * @return int
     * @deprecated since 5.2.0
     */
    public function deleteQueueEntry(int $cronId): int
    {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
        $affected = $this->db->getAffectedRows(
            'DELETE FROM tjobqueue WHERE cronID = :id',
            ['id' => $cronId]
        );

        return $affected + $this->db->getAffectedRows(
            'DELETE FROM tcron WHERE cronID = :id',
            ['id' => $cronId]
        );
    }

    /**
     * @param array $post
     * @return int
     * @deprecated since 5.2.0
     */
    public function addQueueEntry(array $post): int
    {
        \trigger_error(__METHOD__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
        $mapper = new JobTypeToJob();
        try {
            $class = $mapper->map($post['type']);
        } catch (InvalidArgumentException) {
            return -1;
        }
        if ($class === Statusmail::class) {
            $jobs  = $this->db->selectAll('tstatusemail', 'nAktiv', 1);
            $count = 0;
            foreach ($jobs as $job) {
                $ins               = new stdClass();
                $ins->frequency    = (int)$job->nInterval * 24;
                $ins->jobType      = $post['type'];
                $ins->name         = 'statusemail';
                $ins->tableName    = 'tstatusemail';
                $ins->foreignKey   = 'id';
                $ins->foreignKeyID = (int)$job->id;
                $ins->startTime    = \mb_strlen($post['time']) === 5 ? $post['time'] . ':00' : $post['time'];
                $ins->startDate    = (new DateTime($post['date']))->format('Y-m-d H:i:s');
                $this->db->insert('tcron', $ins);
                ++$count;
            }

            return $count;
        }
        $ins            = new stdClass();
        $ins->frequency = (int)$post['frequency'];
        $ins->jobType   = $post['type'];
        $ins->name      = 'manuell@' . \date('Y-m-d H:i:s');
        $ins->startTime = \mb_strlen($post['time']) === 5 ? $post['time'] . ':00' : $post['time'];
        $ins->startDate = (new DateTime($post['date']))->format('Y-m-d H:i:s');

        return $this->db->insert('tcron', $ins);
    }

    /**
     * @return string[]
     * @deprecated since 5.2.0
     */
    public function getAvailableCronJobs(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
        $available = [
            Type::IMAGECACHE,
            Type::STATUSMAIL,
            Type::DATAPROTECTION,
            Type::TOPSELLER,
        ];
        Dispatcher::getInstance()->fire(Event::GET_AVAILABLE_CRONJOBS, ['jobs' => &$available]);

        return $available;
    }

    /**
     * @return JobInterface[]
     * @deprecated since 5.2.0
     */
    public function getJobs(): array
    {
        \trigger_error(__METHOD__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
        $jobs = [];
        $all  = $this->db->getObjects(
            'SELECT tcron.*, tjobqueue.isRunning, tjobqueue.jobQueueID, texportformat.cName AS exportName
                FROM tcron
                LEFT JOIN tjobqueue
                    ON tcron.cronID = tjobqueue.cronID
                LEFT JOIN texportformat
                    ON texportformat.kExportformat = tcron.foreignKeyID
                    AND tcron.tableName = \'texportformat\''
        );
        foreach ($all as $cron) {
            $cron->jobQueueID = (int)($cron->jobQueueID ?? 0);
            $cron->cronID     = (int)$cron->cronID;
            if ($cron->foreignKeyID !== null) {
                $cron->foreignKeyID = (int)$cron->foreignKeyID;
            }
            $cron->frequency = (int)$cron->frequency;
            $cron->isRunning = (int)$cron->isRunning;
            $mapper          = new JobTypeToJob();
            try {
                $class = $mapper->map($cron->jobType);
                $job   = new $class($this->db, $this->logger, $this->hydrator, $this->cache);
                /** @var JobInterface $job */
                $jobs[] = $job->hydrate($cron);
            } catch (InvalidArgumentException) {
                $this->logger->info('Invalid cron job found: ' . $cron->jobType);
            }
        }

        return $jobs;
    }
}
