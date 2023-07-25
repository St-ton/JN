<?php declare(strict_types=1);

namespace JTL\Cron;

use DateTime;
use stdClass;

/**
 * Class QueueEntry
 * @package JTL\Cron
 */
class QueueEntry
{
    /**
     * @var int
     */
    public int $jobQueueID;

    /**
     * @var int
     */
    public int $cronID;

    /**
     * @var int
     */
    public int $foreignKeyID;

    /**
     * @var int
     */
    public int $taskLimit;

    /**
     * @var int
     */
    public int $tasksExecuted;

    /**
     * @var int
     */
    public int $lastProductID;

    /**
     * @var int
     */
    public int $isRunning = 0;

    /**
     * @var string
     */
    public string $jobType;

    /**
     * @var string|null
     */
    public ?string $tableName;

    /**
     * @var string|null
     */
    public ?string $foreignKey;

    /**
     * @var DateTime
     */
    public DateTime $cronStartTime;

    /**
     * @var DateTime
     */
    public DateTime $startTime;

    /**
     * @var DateTime
     */
    public DateTime $lastStart;

    /**
     * @var DateTime
     */
    public DateTime $lastFinish;

    /**
     * @var DateTime
     */
    public DateTime $nextStart;

    /**
     * @var int
     */
    public int $frequency;

    /**
     * compatibility only
     *
     * @var int
     */
    public int $nLimitN;

    /**
     * compatibility only
     *
     * @var int
     */
    public int $nLimitM;

    /**
     * timestamp at which the cronjob processing has started (unix-timestamp)
     *
     * @var int
     * @since 5.3.0
     */
    public int $timestampCronHasStartedAt;

    /**
     * QueueEntry constructor.
     * @param stdClass $data
     * @throws \Exception
     */
    public function __construct(stdClass $data)
    {
        $this->jobQueueID                = (int)$data->jobQueueID;
        $this->cronID                    = (int)$data->cronID;
        $this->foreignKeyID              = (int)$data->foreignKeyID;
        $this->taskLimit                 = (int)$data->taskLimit;
        $this->nLimitN                   = (int)$data->tasksExecuted;
        $this->tasksExecuted             = (int)$data->tasksExecuted;
        $this->nLimitM                   = (int)$data->taskLimit;
        $this->lastProductID             = (int)$data->lastProductID;
        $this->frequency                 = (int)($data->frequency ?? 0);
        $this->jobType                   = $data->jobType;
        $this->tableName                 = $data->tableName;
        $this->foreignKey                = $data->foreignKey;
        $this->cronStartTime             = new DateTime($data->cronStartTime ?? '');
        $this->startTime                 = new DateTime($data->startTime ?? '');
        $this->lastStart                 = new DateTime($data->lastStart ?? '');
        $this->lastFinish                = new DateTime($data->lastFinish ?? '');
        $this->nextStart                 = new DateTime($data->nextStart ?? '');
        $this->timestampCronHasStartedAt = (int)($data->cronHasStartedAt ?? \time());
    }
}
