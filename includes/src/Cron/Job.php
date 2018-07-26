<?php
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
     * @var string
     */
    private $table = '';

    /**
     * @var bool
     */
    private $finished = false;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var
     */
    protected $logger;

    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->logger = $logger;
        $this->setDateLastStarted(new \DateTime());
    }

    /**
     * @return \stdClass|null
     */
    protected function getJobData()
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
    public function setType(string $type)
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
    public function setLimit(int $limit)
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
    public function setID(int $id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getDateLastStarted(): \DateTime
    {
        return $this->dateLastStarted;
    }

    /**
     * @inheritdoc
     */
    public function setDateLastStarted(\DateTime $dateLastStarted)
    {
        $this->dateLastStarted = $dateLastStarted;
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
    public function setForeignKeyID(int $foreignKeyID)
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
    public function setForeignKey(string $foreignKey)
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
    public function setTable(string $table)
    {
        $this->table = $table;
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        $this->setDateLastStarted(new \DateTime());

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
    public function setExecuted(int $executed)
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
    public function setCronID(int $cronID)
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
    public function setFinished(bool $finished)
    {
        $this->finished = $finished;
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
    public function setQueueID(int $queueID)
    {
        $this->queueID = $queueID;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res                  = \get_object_vars($this);
        unset($res['db'], $res['logger']);

        return $res;
    }
}
