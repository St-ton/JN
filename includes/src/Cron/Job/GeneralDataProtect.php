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
     * @var int
     */
    protected int $foreignKey;

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
            (object)['foreignKey' => (string)$this->foreignKey]
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        // --DEBUG-- -------------------------------------------------------------
        require_once('/www/shop5_02/includes/vendor/apache/log4php/src/main/php/Logger.php');
        \Logger::configure('/www/shop5_02/_logging_conf.xml');
        $oLogger = \Logger::getLogger('default');
        // --DEBUG-- --------------------------------------------------------------

        parent::start($queueEntry);
        $this->foreignKey = (int)$queueEntry->foreignKey;

        // use `tcron`.`foreignKey` as a step-storage here
        if ($queueEntry->foreignKey === '') {
            $queueEntry->foreignKey = '0';
        }
        $tableCleaner = new TableCleaner();
        $tableCleaner->executeByStep($this->foreignKey);
        if ($tableCleaner->getIsFinished()) {
            $this->setForeignKey((string)$this->foreignKey++);
        }
        // if ($queueEntry->foreignKey < 0 || $queueEntry->foreignKey >= $tableCleaner->getMethodCount()) {
        //     $this->setFinished(true);
        // } else {
        //     $this->setFinished(false);
        // }
        $this->setFinished($this->foreignKey >= $tableCleaner->getMethodCount());

        return $this;
    }
}
