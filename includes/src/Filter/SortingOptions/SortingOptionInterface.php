<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter\SortingOptions;


use Filter\Join;

/**
 * Interface SortingOptionInterface
 * @package Filter\SortingOptions
 */
interface SortingOptionInterface
{
    /**
     * @return Join
     */
    public function getJoin(): Join;

    /**
     * @param Join $join
     */
    public function setJoin(Join $join);

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
