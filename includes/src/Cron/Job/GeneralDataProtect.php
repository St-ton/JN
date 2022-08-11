<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\GeneralDataProtection\TableCleaner;

/**
 * Class GeneralDataProtect
 * @package JTL\Cron\Job
 */
final class GeneralDataProtect extends Job
{
    /**
     * "ID" of a single GDP task/class
     *
     * @var int
     */
    protected int $taskIdx;

    /**
     * Undocumented variable
     *
     * @var int
     */
    protected int $taskRepetitions;


    /**
     * @inheritDoc
     */
    public function saveProgress(QueueEntry $queueEntry): bool
    {
        parent::saveProgress($queueEntry);
        $this->db->update(
            'tjobqueue',
            'jobQueueID',
            $this->getQueueID(),
            (object)['foreignKey' => (string)$this->taskIdx]
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        // using `tcron`.`foreignKey` as a task index storage and `tcron`.`tasksExecuted` as repetition (down)counter
        $this->taskIdx         = (int)$queueEntry->foreignKey;
        $this->taskRepetitions = (int)$queueEntry->tasksExecuted;
        if ($queueEntry->foreignKey === '') {
            $queueEntry->foreignKey = '0';
        }
        $tableCleaner = new TableCleaner();
        $tableCleaner->executeByStep($this->taskIdx, $this->taskRepetitions);
        $queueEntry->tasksExecuted = $tableCleaner->getTaskRepetitions(); // save the max repetition count of this task
        if ($tableCleaner->getIsFinished()) {
            $this->setForeignKey((string)$this->taskIdx++);
        }
        $this->setFinished($this->taskIdx >= $tableCleaner->getMethodCount());

        return $this;
    }
}
