<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;

use DB\DbInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Job
 * @package Cron
 */
abstract class Job implements JobInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $limit = 100;

    /**
     * @var int
     */
    private $executed = 0;

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var int
     */
    private $cronID = 0;

    /**
     * @var int
     */
    private $queueID = 0;

    /**
     * @var int
     */
    private $foreignKeyID = 0;

    /**
     * @var string
     */
    private $foreignKey = '';

    /**
     * @var \DateTime
     */
    private $dateLastStarted;

    /**
     * @var \DateTime
     */
    private $startTime;

    /**
     * @var string
     */
    private $table = '';

    /**
     * @var bool
     */
    private $finished = false;

    /**
     * @var bool
     */
    private $running = false;

    /**
     * @var int
     */
    private $frequency = 24;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JobHydrator
     */
    protected $hydrator;

    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, JobHydrator $hydrator)
    {
        $this->db       = $db;
        $this->logger   = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        return $this->hydrator->hydrate($this, $data);
    }

    /**
     * @return \stdClass|null
     */
    protected function getJobData(): ?\stdClass
    {
        return $this->getForeignKeyID() > 0 && $this->getForeignKey() !== '' && $this->getTable() !== ''
            ? $this->db->select(
                $this->getTable(),
                $this->getForeignKey(),
                $this->getForeignKeyID()
            )
            : null;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @inheritdoc
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @inheritdoc
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setID(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getDateLastStarted(): ?\DateTime
    {
        return $this->dateLastStarted;
    }

    /**
     * @inheritdoc
     */
    public function setDateLastStarted($dateLastStarted): void
    {
        $this->dateLastStarted = \is_string($dateLastStarted)
            ? new \DateTime($dateLastStarted)
            : $dateLastStarted;
    }

    /**
     * @param string $dateLastStarted
     */
    public function setLastStarted(?string $dateLastStarted): void
    {
        $this->dateLastStarted = $dateLastStarted === null ? null : new \DateTime($dateLastStarted);
    }

    /**
     * @inheritdoc
     */
    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    /**
     * @inheritdoc
     */
    public function setStartTime($startTime): void
    {
        $this->startTime = \is_string($startTime)
            ? new \DateTime($startTime)
            : $startTime;
    }

    /**
     * @inheritdoc
     */
    public function getForeignKeyID(): int
    {
        return $this->foreignKeyID;
    }

    /**
     * @inheritdoc
     */
    public function setForeignKeyID(int $foreignKeyID): void
    {
        $this->foreignKeyID = $foreignKeyID;
    }

    /**
     * @inheritdoc
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * @inheritdoc
     */
    public function setForeignKey(string $foreignKey): void
    {
        $this->foreignKey = $foreignKey;
    }

    /**
     * @inheritdoc
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @inheritdoc
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        $this->setDateLastStarted(new \DateTime());
        $this->db->update(
            'tjobqueue',
            'kJobQueue',
            $queueEntry->kJobQueue,
            (object)['nInArbeit' => $queueEntry->nInArbeit]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExecuted(): int
    {
        return $this->executed;
    }

    /**
     * @inheritdoc
     */
    public function setExecuted(int $executed): void
    {
        $this->executed = $executed;
    }

    /**
     * @inheritdoc
     */
    public function getCronID(): int
    {
        return $this->cronID;
    }

    /**
     * @inheritdoc
     */
    public function setCronID(int $cronID): void
    {
        $this->cronID = $cronID;
    }

    /**
     * @inheritdoc
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @inheritdoc
     */
    public function setFinished(bool $finished): void
    {
        $this->finished = $finished;
    }

    /**
     * @inheritdoc
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * @inheritdoc
     */
    public function setRunning(bool $running): void
    {
        $this->running = $running;
    }

    /**
     * @inheritdoc
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    /**
     * @inheritdoc
     */
    public function setFrequency(int $frequency): void
    {
        $this->frequency = $frequency;
    }

    /**
     * @inheritdoc
     */
    public function getQueueID(): int
    {
        return $this->queueID;
    }

    /**
     * @inheritdoc
     */
    public function setQueueID(int $queueID): void
    {
        $this->queueID = $queueID;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res = \get_object_vars($this);
        unset($res['db'], $res['logger']);

        return $res;
    }
}
