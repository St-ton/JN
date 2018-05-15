<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


/**
 * Class FilterStateSQL
 * @package Filter
 */
interface FilterStateSQLInterface
{
    /**
     * @return array
     */
    public function getHaving(): array;

    /**
     * @param array $having
     */
    public function setHaving(array $having);

    /**
     * @param string $having
     * @return array
     */
    public function addHaving(string $having): array;

    /**
     * @return array
     */
    public function getConditions(): array;

    /**
     * @param array $conditions
     */
    public function setConditions(array $conditions);

    /**
     * @param string $condition
     * @return array
     */
    public function addCondition(string $condition): array;

    /**
     * @return FilterJoinInterface[]
     */
    public function getJoins(): array;

    /**
     * @return FilterJoinInterface[]
     */
    public function getDeduplicatedJoins(): array;

    /**
     * @param FilterJoinInterface[] $joins
     */
    public function setJoins(array $joins);

    /**
     * @param FilterJoinInterface $join
     * @return array
     */
    public function addJoin(FilterJoinInterface $join): array;

    /**
     * @return string
     */
    public function getSelect(): string;

    /**
     * @param string $select
     */
    public function setSelect(string $select);

    /**
     * @param string $select
     * @return string
     */
    public function addSelect(string $select): string;
}
