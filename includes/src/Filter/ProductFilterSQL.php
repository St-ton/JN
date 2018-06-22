<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

use function Functional\reduce_left;
use Mapper\SortingType;

/**
 * Class ProductFilterSQL
 */
class ProductFilterSQL implements ProductFilterSQLInterface
{
    /**
     * @var ProductFilter
     */
    private $productFilter;

    /**
     * @var array
     */
    private $conf;

    /**
     * ProductFilterSQL constructor.
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        $this->productFilter = $productFilter;
        $this->conf          = $productFilter->getConfig();
    }

    /**
     * @inheritdoc
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
    ): string {
        if ($sort === null) {
            $sort    = $this->productFilter->getSorting()->getActiveSorting();
            $joins[] = $sort->getJoin();
            $sort    = $sort->getOrderBy();
        }
        $joins[] = (new FilterJoin())
            ->setComment('product visiblity join from getBaseQuery')
            ->setType('LEFT JOIN')
            ->setTable('tartikelsichtbarkeit')
            ->setOrigin(__CLASS__)
            ->setOn('tartikel.kArtikel = tartikelsichtbarkeit.kArtikel 
                        AND tartikelsichtbarkeit.kKundengruppe = ' . $this->productFilter->getCustomerGroupID());
        // remove duplicate joins
        $checked = [];
        $joins   = reduce_left($joins, function(FilterJoinInterface $value, $i, $c, $reduction) use (&$checked) {
            $key = $value->getTable();
            if (!in_array($key, $checked, true)) {
                $checked[]   = $key;
                $reduction[] = $value;
            }

            return $reduction;
        }, []);
        // default base conditions
        $conditions[] = 'tartikelsichtbarkeit.kArtikel IS NULL';

        $showChildProducts = $this->productFilter->showChildProducts();
        if ($showChildProducts === 2
            || ($showChildProducts === 1
                && ($type === 'filter' || $this->productFilter->getFilterCount() > 0))
        ) {
            $conditions[] = '(tartikel.kVaterArtikel > 0 
                                OR NOT EXISTS 
                                    (SELECT 1 FROM tartikel cps WHERE cps.kVaterArtikel = tartikel.kArtikel))';
        } else {
            $conditions[] = 'tartikel.kVaterArtikel = 0';
        }
        $conditions[] = $this->getStockFilterSQL(false);
        // remove empty conditions
        $conditions = array_filter($conditions);
        executeHook(HOOK_PRODUCTFILTER_GET_BASE_QUERY, [
            'select'        => &$select,
            'joins'         => &$joins,
            'conditions'    => &$conditions,
            'groupBy'       => &$groupBy,
            'having'        => &$having,
            'order'         => &$sort,
            'limit'         => &$limit,
            'productFilter' => $this
        ]);
        // merge FilterQuery-Conditions
        $filterQueryIndices = [];
        $filterQueries      = array_filter($conditions, function ($f) {
            return is_object($f) && get_class($f) === FilterQuery::class;
        });
        foreach ($filterQueries as $idx => $condition) {
            /** @var FilterQueryInterface $condition */
            if (count($filterQueryIndices) === 0) {
                $filterQueryIndices[] = $idx;
                continue;
            }
            $found        = false;
            $currentWhere = $condition->getWhere();
            foreach ($filterQueryIndices as $i) {
                $check = $conditions[$i];
                /** @var FilterQueryInterface $check */
                if ($currentWhere === $check->getWhere()) {
                    $found = true;
                    $check->setParams(array_merge_recursive($check->getParams(), $condition->getParams()));
                    unset($conditions[$idx]);
                    break;
                }
            }
            if ($found === false) {
                $filterQueryIndices[] = $idx;
            }
        }
        // build sql string
        $cond = implode(' AND ', array_map(function ($a) {
            if (is_string($a) || (is_object($a) && get_class($a) === FilterQuery::class)) {
                return $a;
            }

            return '(' . implode(' AND ', $a) . ')';
        }, $conditions));

        return 'SELECT ' . implode(', ', $select) . '
            FROM tartikel ' . implode("\n", $joins) . "\n" .
            (empty($cond) ? '' : (' WHERE ' . $cond . "\n")) .
            (empty($groupBy) ? '' : ('#default group by' . "\n" . 'GROUP BY ' . implode(', ', $groupBy) . "\n")) .
            (implode(' AND ', $having) . "\n") .
            (empty($sort) ? '' : ('#limit sql' . "\n" . 'ORDER BY ' . $sort)) .
            (empty($limit) ? '' : ('#order by sql' . "\n" . 'LIMIT ' . $limit));
    }

    /**
     * @inheritdoc
     */
    public function getStockFilterSQL($withAnd = true): string
    {
        $filterSQL  = '';
        $filterType = (int)$this->conf['global']['artikel_artikelanzeigefilter'];
        if ($filterType === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER
            || $filterType === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
        ) {
            $or = $filterType === EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL
                ? " OR tartikel.cLagerKleinerNull = 'Y'"
                : '';
            $filterSQL = ($withAnd === true ? ' AND ' : ' ') .
                "(tartikel.cLagerBeachten != 'Y'
                    OR tartikel.fLagerbestand > 0
                    OR (tartikel.cLagerVariation = 'Y'
                        AND (
                            SELECT MAX(teigenschaftwert.fLagerbestand)
                            FROM teigenschaft
                            INNER JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                            WHERE teigenschaft.kArtikel = tartikel.kArtikel
                        ) > 0
                    )" . $or .
                ")";
        }
        executeHook(HOOK_STOCK_FILTER, [
            'conf'      => $filterType,
            'filterSQL' => &$filterSQL
        ]);

        return $filterSQL;
    }
}
