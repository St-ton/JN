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
    public function getOrder(): \stdClass
    {
        $Artikelsortierung = (int)$this->conf['artikeluebersicht']['artikeluebersicht_artikelsortierung'];
        $sort              = new \stdClass();
        $sort->join        = (new FilterJoin())->setOrigin(__CLASS__);
        if (isset($_SESSION['Usersortierung'])) {
            $mapper = new SortingType();
            $Artikelsortierung = $mapper->mapUserSorting($_SESSION['Usersortierung']);
        }
        $_SESSION['Usersortierung'] = $Artikelsortierung;
        if ($_SESSION['Usersortierung'] === SEARCH_SORT_STANDARD && $this->productFilter->getSort() > 0) {
            $Artikelsortierung = $this->productFilter->getSort();
        }
//        \Shop::dbg($Artikelsortierung, false, '$Artikelsortierung:');
//        \Shop::dbg($this->productFilter->getSort(), false, '$this->productFilter->getSort():');
//        \Shop::dbg($this->productFilter->getSorting(), false, '$this->productFilter->getSorting():');
//        \Shop::dbg($this->productFilter->getSorting()->sortingOptions, true, 'sortingOptions:');
        switch ($Artikelsortierung) {
            case SEARCH_SORT_STANDARD:
                $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                if ($this->productFilter->getCategory()->getValue() > 0) {
                    $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                } elseif (isset($_SESSION['Usersortierung'])
                    && $_SESSION['Usersortierung'] === SEARCH_SORT_STANDARD
                    && $this->productFilter->getSearch()->getSearchCacheID() > 0
                ) {
                    $sort->orderBy = 'jSuche.nSort'; // was tsuchcachetreffer in 4.06, but is aliased to jSuche
                }
                break;
            case SEARCH_SORT_NAME_ASC:
                $sort->orderBy = 'tartikel.cName';
                break;
            case SEARCH_SORT_NAME_DESC:
                $sort->orderBy = 'tartikel.cName DESC';
                break;
            case SEARCH_SORT_PRICE_ASC:
                $sort->orderBy = 'tpreise.fVKNetto, tartikel.cName';
                $sort->join->setComment('join from SORT by price ASC')
                           ->setType('JOIN')
                           ->setTable('tpreise')
                           ->setOn('tartikel.kArtikel = tpreise.kArtikel 
                                        AND tpreise.kKundengruppe = ' . $this->productFilter->getCustomerGroupID());
                break;
            case SEARCH_SORT_PRICE_DESC:
                $sort->orderBy = 'tpreise.fVKNetto DESC, tartikel.cName';
                $sort->join->setComment('join from SORT by price DESC')
                           ->setType('JOIN')
                           ->setTable('tpreise')
                           ->setOn('tartikel.kArtikel = tpreise.kArtikel 
                                        AND tpreise.kKundengruppe = ' . $this->productFilter->getCustomerGroupID());
                break;
            case SEARCH_SORT_EAN:
                $sort->orderBy = 'tartikel.cBarcode, tartikel.cName';
                break;
            case SEARCH_SORT_NEWEST_FIRST:
                $sort->orderBy = 'tartikel.dErstellt DESC, tartikel.cName';
                break;
            case SEARCH_SORT_PRODUCTNO:
                $sort->orderBy = 'tartikel.cArtNr, tartikel.cName';
                break;
            case SEARCH_SORT_AVAILABILITY:
                $sort->orderBy = 'tartikel.fLagerbestand DESC, tartikel.cLagerKleinerNull DESC, tartikel.cName';
                break;
            case SEARCH_SORT_WEIGHT:
                $sort->orderBy = 'tartikel.fGewicht, tartikel.cName';
                break;
            case SEARCH_SORT_DATEOFISSUE:
                $sort->orderBy = 'tartikel.dErscheinungsdatum DESC, tartikel.cName';
                break;
            case SEARCH_SORT_BESTSELLER:
                $sort->orderBy = 'tbestseller.fAnzahl DESC, tartikel.cName';
                $sort->join->setComment('join from SORT by bestseller')
                           ->setType('LEFT JOIN')
                           ->setTable('tbestseller')
                           ->setOn('tartikel.kArtikel = tbestseller.kArtikel');
                break;
            case SEARCH_SORT_RATING:
                $sort->orderBy = 'tbewertung.nSterne DESC, tartikel.cName';
                $sort->join->setComment('join from SORT by rating')
                           ->setType('LEFT JOIN')
                           ->setTable('tbewertung')
                           ->setOn('tbewertung.kArtikel = tartikel.kArtikel');
                break;
            default:
                die('default sort!');
                $sort->orderBy = 'tartikel.nSort, tartikel.cName';
                break;
        }
//        \Shop::dbg($sort, false, 'getOrder() returns:');

        return $sort;
    }

    /**
     * @inheritdoc
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
    ): string {
        if ($order === null) {
            $orderData = $this->getOrder();
            $joins[]   = $orderData->join;
            $order     = $orderData->orderBy;
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
            'order'         => &$order,
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
            (empty($order) ? '' : ('#limit sql' . "\n" . 'ORDER BY ' . $order)) .
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
