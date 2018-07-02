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
     * @param FilterStateSQLInterface $source
     * @return FilterStateSQLInterface
     */
    public function from(FilterStateSQLInterface $source): FilterStateSQLInterface;

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
     * @return array
     */
    public function getSelect(): array;

    /**
     * @param array $select
     */
    public function setSelect(array $select);

    /**
     * @param string $select
     * @return array
     */
    public function addSelect(string $select): array;

    /**
     * @return string|null
     */
    public function getOrderBy();

    /**
     * @param string|null $orderBy
     */
    public function setOrderBy($orderBy);

    /**
     * @return string
     */
    public function getLimit(): string;

    /**
     * @param string $limit
     */
    public function setLimit(string $limit);

    /**
     * @return array
     */
    public function getGroupBy(): array;

    /**
     * @param string $groupBy
     * @return array
     */
    public function addGroupBy(string $groupBy): array;

    /**
     * @param array $groupBy
     */
    public function setGroupBy(array $groupBy);
}
