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
        // --DEBUG-- -------------------------------------------------------------
        require_once('/www/shop5_02/includes/vendor/apache/log4php/src/main/php/Logger.php');
        \Logger::configure('/www/shop5_02/_logging_conf.xml');
        $oLogger = \Logger::getLogger('default');
        // --DEBUG-- -------------------------------------------------------------
        $oLogger->debug('saving progress ...');   // --DEBUG--

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
        // --DEBUG-- -------------------------------------------------------------
        require_once('/www/shop5_02/includes/vendor/apache/log4php/src/main/php/Logger.php');
        \Logger::configure('/www/shop5_02/_logging_conf.xml');
        $oLogger = \Logger::getLogger('default');
        // --DEBUG-- -------------------------------------------------------------

        parent::start($queueEntry);

        // using `tcron`.`foreignKey` as a task index storage and `tcron`.`tasksExecuted` as repetition (down)counter
        $this->taskIdx         = (int)$queueEntry->foreignKey;
        $this->taskRepetitions = (int)$queueEntry->tasksExecuted;

        // $oLogger->debug('Q-entry: '.print_r($queueEntry,true)); // --DEBUG--

        if ($queueEntry->foreignKey === '') {
            $queueEntry->foreignKey = '0';
        }
        $tableCleaner = new TableCleaner();
        $tableCleaner->executeByStep($this->taskIdx, $this->taskRepetitions);  // --TRYOUT-- second param!

        $queueEntry->tasksExecuted = $tableCleaner->getTaskRepetitions();     // --TRYOUT-- save the max repetition count of this task
        if ($tableCleaner->getIsFinished()) {
            $this->setForeignKey((string)$this->taskIdx++);
        }
        $this->setFinished($this->taskIdx >= $tableCleaner->getMethodCount());

        return $this;
    }
}
