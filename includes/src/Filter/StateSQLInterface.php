<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


/**
 * Class StateSQLInterface
 * @package Filter
 */
interface StateSQLInterface
{
    /**
     * @param StateSQLInterface $source
     * @return StateSQLInterface
     */
    public function from(StateSQLInterface $source): StateSQLInterface;

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
     * @return JoinInterface[]
     */
    public function getJoins(): array;

    /**
     * @return JoinInterface[]
     */
    public function getDeduplicatedJoins(): array;

    /**
     * @param JoinInterface[] $joins
     */
    public function setJoins(array $joins);

    /**
     * @param JoinInterface $join
     * @return array
     */
    public function addJoin(JoinInterface $join): array;

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
