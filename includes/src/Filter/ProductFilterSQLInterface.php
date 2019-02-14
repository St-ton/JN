<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Filter;

/**
 * Interface ProductFilterSQLInterface
 * @package JTL\Filter
 */
interface ProductFilterSQLInterface
{
    /**
     * @param StateSQLInterface $state
     * @param string            $type
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getBaseQuery(StateSQLInterface $state, string $type = 'filter'): string;

    /**
     * @param bool $withAnd
     * @return string
     */
    public function getStockFilterSQL($withAnd = true): string;
}
