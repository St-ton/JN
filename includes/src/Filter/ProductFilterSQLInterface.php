<?php
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
     * @param array  $select
     * @param array  $joins
     * @param array  $conditions
     * @param array  $having
     * @param string $sort
     * @param string $limit
     * @param array  $groupBy
     * @param string $type
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getBaseQuery(
        array $select = ['tartikel.kArtikel'],
        array $joins,
        array $conditions,
        array $having = [],
        $sort = null,
        $limit = '',
        array $groupBy = ['tartikel.kArtikel'],
        $type = 'filter'
    ): string;

    /**
     * @param bool $withAnd
     * @return string
     */
    public function getStockFilterSQL($withAnd = true): string;
}
