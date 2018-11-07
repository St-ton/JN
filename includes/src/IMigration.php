<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Interface IMigration
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
