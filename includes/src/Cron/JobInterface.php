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
interface JobInterface
{
    /**
     * JobInterface constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     * @param JobHydrator     $hydrator
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, JobHydrator $hydrator);

    /**
     * @param QueueEntry|\stdClass $data
     * @return object
     */
    public function hydrate($data);

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void;

    /**
     * @return int
     */
    public function getID(): int;

    /**
     * @param int $id
     */
    public function setID(int $id): void;

    /**
     * @return \DateTime|null
     */
    public function getDateLastStarted(): ?\DateTime;

    /**
     * @param \DateTime|string|null $dateLastStarted
     */
    public function setDateLastStarted($dateLastStarted): void;

    /**
     * @param string|null $dateLastStarted
     */
    public function setLastStarted(?string $dateLastStarted): void;

    /**
     * @return \DateTime|null
     */
    public function getStartTime(): ?\DateTime;

    /**
     * @param \DateTime|string|null $startTime
     */
    public function setStartTime($startTime): void;

    /**
     * @return int
     */
    public function getForeignKeyID(): int;

    /**
     * @param int $foreignKeyID
     */
    public function setForeignKeyID(int $foreignKeyID): void;

    /**
     * @return string
     */
    public function getForeignKey(): string;

    /**
     * @param string $foreignKey
     */
    public function setForeignKey(string $foreignKey): void;

    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * @param string $table
     */
    public function setTable(string $table): void;

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
    public function setExecuted(int $executed): void;

    /**
     * @return int
     */
    public function getCronID(): int;

    /**
     * @param int $cronID
     */
    public function setCronID(int $cronID): void;

    /**
     * @return bool
     */
    public function isFinished(): bool;

    /**
     * @param bool $finished
     */
    public function setFinished(bool $finished): void;

    /**
     * @return bool
     */
    public function isRunning(): bool;

    /**
     * @param bool $running
     */
    public function setRunning(bool $running): void;

    /**
     * @return int
     */
    public function getFrequency(): int;

    /**
     * @param int $frequency
     */
    public function setFrequency(int $frequency): void;

    /**
     * @return int
     */
    public function getQueueID(): int;

    /**
     * @param int $queueID
     */
    public function setQueueID(int $queueID): void;
}
