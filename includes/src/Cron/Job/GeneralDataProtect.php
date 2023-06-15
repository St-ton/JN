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
     * max repetitions of one task
     *
     * @var int
     */
    protected int $taskRepetitions;

    /**
     * last ID for `CleanupGuestAccountsWithoutOrders`
     *
     * @var integer
     */
    protected int $lastProductID;

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
        // using `tjobqueue`.`foreignKey` as a task index storage
        $this->taskIdx = (int)$queueEntry->foreignKey;
        // using `tjobqueue`.`lastProductID` as "index of work" in one table
        $this->lastProductID = (int)$queueEntry->lastProductID;
        // using `tjobqueue`.`tasksExecuted` as repetition "down counter"
        $this->taskRepetitions = (int)$queueEntry->tasksExecuted;
        if ($queueEntry->foreignKey === '') {
            $queueEntry->foreignKey = '0';
        }
        $tableCleaner = new TableCleaner();
        $tableCleaner->executeByStep(
            $this->taskIdx,
            $this->taskRepetitions,
            $this->lastProductID
        );
        $queueEntry->tasksExecuted = $tableCleaner->getTaskRepetitions(); // save the max repetition count of this task
        $queueEntry->lastProductID = $tableCleaner->getLastProductID(); // save last postion (CleanupGuestAccountsWithoutOrders)
        if ($tableCleaner->getIsFinished()) {
            $this->setForeignKey((string)$this->taskIdx++);
        }
        $this->setFinished($this->taskIdx >= $tableCleaner->getMethodCount());

        return $this;
    }
}
