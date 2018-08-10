<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;

use Filter\FilterJoin;

/**
 * Interface SortingOptionInterface
 * @package Filter\SortingOptions
 */
interface SortingOptionInterface
{
    /**
     * @return FilterJoin
     */
    public function getJoin(): FilterJoin;

    /**
     * @param FilterJoin $join
     */
    public function setJoin(FilterJoin $join);

    /**
     * @return string
     */
    public function getOrderBy(): string;

    /**
     * @param string $orderBy
     */
    public function setOrderBy(string $orderBy);

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @param int $priority
     */
    public function setPriority(int $priority);

    /**
     * @return int|string|array
     */
    public function getValue();
}
