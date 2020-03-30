<?php

namespace JTL\Update;

use DateTime;

/**
 * Interface IMigration
 * @package JTL\Update
 */
interface IMigration
{
    /**
     * @var string
     */
    public const UP = 'up';

    /**
     * @var string
     */
    public const DOWN = 'down';

    /**
     * @return mixed
     */
    public function up();

    /**
     * @return mixed
     */
    public function down();

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getAuthor();

    /**
     * @return null|string
     */
    public function getDescription();

    /**
     * @return DateTime
     */
    public function getCreated();

    /**
     * @return bool
     */
    public function doDeleteData(): bool;

    /**
     * @param bool $deleteData
     */
    public function setDeleteData(bool $deleteData): void;
}
