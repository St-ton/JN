<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


/**
 * Class FilterJoin
 * @package Filter
 */
interface FilterJoinInterface
{
    /**
     * @param string $origin
     * @return $this
     */
    public function setOrigin(string $origin): FilterJoinInterface;

    /**
     * @return string|null
     */
    public function getOrigin(): string;

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type): FilterJoinInterface;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * @param string $table
     * @return $this
     */
    public function setTable(string $table): FilterJoinInterface;

    /**
     * @return string
     */
    public function getComment(): string;

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment): FilterJoinInterface;

    /**
     * @return string
     */
    public function getOn(): string;

    /**
     * @param string $on
     * @return $this
     */
    public function setOn(string $on): FilterJoinInterface;

    /**
     * @return string
     */
    public function getSQL(): string;
}
