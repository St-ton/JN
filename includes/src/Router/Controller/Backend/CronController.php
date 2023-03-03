<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use DateTimeImmutable;
use JTL\Backend\Permissions;
use JTL\Cron\Job\Statusmail;
use JTL\Cron\JobHydrator;
use JTL\Cron\JobInterface;
use JTL\Cron\Type;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Mapper\JobTypeToJob;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class CronController
 * @package JTL\Router\Controller\Backend
 */
class CronController extends AbstractBackendController
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var JobHydrator
     */
    private JobHydrator $hydrator;

    /**
     * C@inheritdoc
     */
    public function init(): void
    {
        $this->logger   = Shop::Container()->getLogService();
        $this->hydrator = new JobHydrator();
        $this->getText->loadAdminLocale('pages/cron');
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::CRON_VIEW);

        $deleted  = 0;
        $updated  = 0;
        $inserted = 0;
        $tab      = 'overview';
        if (Form::validateToken()) {
            if (isset($_POST['reset'])) {
                $updated = $this->resetQueueEntry(Request::postInt('reset'));
            } elseif (isset($_POST['delete'])) {
                $deleted = $this->deleteQueueEntry(Request::postInt('delete'));
            } elseif (Request::postInt('add-cron') === 1) {
                $inserted = $this->addQueueEntry($_POST);
                $tab      = 'add-cron';
            } elseif (Request::postVar('a') === 'saveSettings') {
                $tab = 'settings';
                $this->saveAdminSectionSettings(\CONF_CRON, $_POST);
            }
        }
        $this->getAdminSectionSettings(\CONF_CRON);

        return $smarty->assign('jobs', $this->getJobs())
            ->assign('deleted', $deleted)
            ->assign('updated', $updated)
            ->assign('inserted', $inserted)
            ->assign('available', $this->getAvailableCronJobs())
            ->assign('tab', $tab)
            ->assign('route', $this->route)
            ->getResponse('cron.tpl');
    }

    /**
     * @param int $jobQueueId
     * @return int
     */
    public function resetQueueEntry(int $jobQueueId): int
    {
        return $this->db->update('tjobqueue', 'jobQueueID', $jobQueueId, (object)['isRunning' => 0]);
    }

    /**
     * @param int $cronId
     * @return int
     */
    public function deleteQueueEntry(int $cronId): int
    {
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
     */
    public function addQueueEntry(array $post): int
    {
        $mapper = new JobTypeToJob();
        try {
            $class = $mapper->map($post['type']);
        } catch (\InvalidArgumentException) {
            return -1;
        }
        $startDate = new DateTimeImmutable($post['date']);
        $startTime = \mb_strlen($post['time']) === 5 ? $post['time'] . ':00' : $post['time'];
        if ($class === Statusmail::class) {
            $count = 0;
            foreach ($this->db->selectAll('tstatusemail', 'nAktiv', 1) as $job) {
                $ins               = new stdClass();
                $ins->frequency    = (int)$job->nInterval * 24;
                $ins->jobType      = $post['type'];
                $ins->name         = 'statusemail';
                $ins->tableName    = 'tstatusemail';
                $ins->foreignKey   = 'id';
                $ins->foreignKeyID = (int)$job->id;
                $ins->startTime    = $startTime;
                $ins->startDate    = $startDate->format('Y-m-d H:i:s');
                $ins->nextStart    = $startDate->format('Y-m-d') . ' ' . $startTime;
                $this->db->insert('tcron', $ins);
                ++$count;
            }

            return $count;
        }
        $ins            = new stdClass();
        $ins->frequency = (int)$post['frequency'];
        $ins->jobType   = $post['type'];
        $ins->name      = 'manuell@' . \date('Y-m-d H:i:s');
        $ins->startTime = $startTime;
        $ins->startDate = $startDate->format('Y-m-d H:i:s');
        $ins->nextStart = $startDate->format('Y-m-d') . ' ' . $startTime;

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
            Type::DATAPROTECTION,
            Type::TOPSELLER,
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
            } catch (\InvalidArgumentException) {
                $this->logger->info('Invalid cron job found: {type}', ['type' => $cron->jobType]);
            }
        }

        return $jobs;
    }
}
