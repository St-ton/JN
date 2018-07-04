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
interface JobInterface
{
    /**
     * JobInterface constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db);

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type);

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @param int $limit
     */
    public function setLimit(int $limit);

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id);

    /**
     * @return \DateTime
     */
    public function getDateLastStarted(): \DateTime;

    /**
     * @param \DateTime $dateLastStarted
     */
    public function setDateLastStarted(\DateTime $dateLastStarted);

    /**
     * @return int
     */
    public function getForeignKeyID(): int;

    /**
     * @param int $foreignKeyID
     */
    public function setForeignKeyID(int $foreignKeyID);

    /**
     * @return string
     */
    public function getForeignKey(): string;

    /**
     * @param string $foreignKey
     */
    public function setForeignKey(string $foreignKey);

    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * @param string $table
     */
    public function setTable(string $table);

    /**
     * @param QueueEntry $queueEntry
     * @return JobInterface
     */
    public function start(QueueEntry $queueEntry): JobInterface;

    /**
     * @return int
     */
    public function getExecuted(): int;

    /**
     * @param int $executed
     */
    public function setExecuted(int $executed);

    /**
     * @return int
     */
    public function getCronID(): int;

    /**
     * @param int $cronID
     */
    public function setCronID(int $cronID);

    /**
     * @return bool
     */
    public function isFinished(): bool;

    /**
     * @param bool $finished
     */
    public function setFinished(bool $finished);
}
