<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


/**
 * Class FilterQuery
 * @package Filter
 */
interface FilterQueryInterface
{
    /**
     * @param string $where
     * @return $this
     */
    public function setWhere(string $where): FilterQueryInterface;

    /**
     * @return string
     */
    public function getWhere(): string;

    /**
     * @param string $origin
     * @return $this
     */
    public function setOrigin(string $origin): FilterQueryInterface;

    /**
     * @return string
     */
    public function getOrigin(): string;

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): FilterQueryInterface;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return mixed
     */
    public function getTable(): string;

    /**
     * @param string $table
     * @return $this
     */
    public function setTable(string $table): FilterQueryInterface;

    /**
     * @return string
     */
    public function getComment(): string;

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment): FilterQueryInterface;

    /**
     * @return string
     */
    public function getOn(): string;

    /**
     * @param string $on
     * @return $this
     */
    public function setOn(string $on): FilterQueryInterface;

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params): FilterQueryInterface;

    /**
     * @param array $params
     * @return $this
     */
    public function addParams(array $params): FilterQueryInterface;

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @return string
     */
    public function getSQL(): string;
}
