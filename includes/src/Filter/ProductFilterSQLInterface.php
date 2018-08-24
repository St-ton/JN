<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;


/**
 * Class ProductFilterSQL
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
