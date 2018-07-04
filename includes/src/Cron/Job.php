<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;


use DB\DbInterface;

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
    private $db;

    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setID(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastStarted(): \DateTime
    {
        return $this->dateLastStarted;
    }

    /**
     * @param \DateTime $dateLastStarted
     */
    public function setDateLastStarted(\DateTime $dateLastStarted)
    {
        $this->dateLastStarted = $dateLastStarted;
    }

    /**
     * @return int
     */
    public function getForeignKeyID(): int
    {
        return $this->foreignKeyID;
    }

    /**
     * @param int $foreignKeyID
     */
    public function setForeignKeyID(int $foreignKeyID)
    {
        $this->foreignKeyID = $foreignKeyID;
    }

    /**
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * @param string $foreignKey
     */
    public function setForeignKey(string $foreignKey)
    {
        $this->foreignKey = $foreignKey;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
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
     * @return int
     */
    public function getExecuted(): int
    {
        return $this->executed;
    }

    /**
     * @param int $executed
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
}
