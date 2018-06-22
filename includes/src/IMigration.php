<?php

/**
 * Interface IMigration
 */
interface IMigration
{
    /**
     * @var string
     */
    const UP = 'up';

    /**
     * @var string
     */
    const DOWN = 'down';

    /**
     * @return bool
     */
    public function up();

    /**
     * @return bool
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
}
