<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use InvalidArgumentException;
use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Export\ExporterFactory;
use stdClass;

/**
 * Class Export
 * @package JTL\Cron\Job
 */
final class Export extends Job
{
    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);
        if (\JOBQUEUE_LIMIT_M_EXPORTE > 0) {
            $this->setLimit((int)\JOBQUEUE_LIMIT_M_EXPORTE);
        }
        if ($this->getName() === null && \is_a($data, stdClass::class) && !empty($data->exportName)) {
            $this->setName($data->exportName);
        }

        return $this;
    }

    /**
     * @param QueueEntry $queueEntry
     * @return bool
     */
    public function updateExportformatQueueBearbeitet(QueueEntry $queueEntry): bool
    {
        if ($queueEntry->jobQueueID <= 0) {
            return false;
        }
        $this->db->delete('texportformatqueuebearbeitet', 'kJobQueue', (int)$queueEntry->jobQueueID);

        $ins                   = new stdClass();
        $ins->kJobQueue        = $queueEntry->jobQueueID;
        $ins->kExportformat    = $queueEntry->foreignKeyID;
        $ins->nLimitN          = $queueEntry->tasksExecuted;
        $ins->nLimitM          = $queueEntry->taskLimit;
        $ins->nInArbeit        = $queueEntry->isRunning;
        $ins->dStartZeit       = $queueEntry->startTime->format('Y-m-d H:i');
        $ins->dZuletztGelaufen = $queueEntry->lastStart->format('Y-m-d H:i');

        $this->db->insert('texportformatqueuebearbeitet', $ins);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $factory  = new ExporterFactory($this->db, $this->logger, $this->cache);
        $ef       = $factory->getExporter($this->getForeignKeyID());
        $finished = false;
        try {
            $finished = $ef->startExport(
                $this->getForeignKeyID(),
                $queueEntry,
                false,
                false,
                true
            );
        } catch (InvalidArgumentException) {
        }
        $this->updateExportformatQueueBearbeitet($queueEntry);
        $this->setFinished($finished);

        return $this;
    }
}
