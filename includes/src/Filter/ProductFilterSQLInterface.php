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
     * @return \stdClass
     */
    public function getOrder(): \stdClass;

    /**
     * @param array  $select
     * @param array  $joins
     * @param array  $conditions
     * @param array  $having
     * @param string $order
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
        $order = null,
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
