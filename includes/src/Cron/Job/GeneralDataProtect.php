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
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);

        return $this;
    }

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
            (object)['foreignKey' => (string)$queueEntry->foreignKey]
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        // use `tcron`.`foreignKey` as a step-storage here
        if ($queueEntry->foreignKey === '') {
            $queueEntry->foreignKey = '0';
        }
        $tableCleaner = new TableCleaner();
        $tableCleaner->executeByStep((int)$queueEntry->foreignKey);
        if (!$tableCleaner->getIsUnfinished()) {
            $this->setForeignKey((string)((int)$queueEntry->foreignKey++));
        }
        $this->saveProgress($queueEntry);
        if ($queueEntry->foreignKey < 0 || $queueEntry->foreignKey >= $tableCleaner->getMethodCount()) {
            $this->setFinished(true);
        } else {
            $this->setFinished(false);
        }

        return $this;
    }
}
