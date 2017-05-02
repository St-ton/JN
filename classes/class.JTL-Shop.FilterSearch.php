<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterSearch
 */
class FilterSearch extends AbstractFilter
{
    use FilterItemTrait;

    /**
     * @var int
     */
    public $kSuchanfrage = 0;

    /**
     * @var string
     */
    public $cSuche;

    /**
     * @var int
     */
    public $kSuchCache = 0;

    /**
     * @var string
     */
    public $Fehler;

    /**
     * FilterSearch constructor.
     *
     * @param Navigationsfilter $naviFilter
     * @param int|null          $languageID
     * @param int|null          $customerGroupID
     * @param array|null        $config
     * @param array|null        $languages
     */
    public function __construct($naviFilter, $languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($naviFilter, $languageID, $customerGroupID, $config, $languages);
        $this->isCustom    = false;
        $this->urlParam    = 'sf';
        $this->urlParamSEO = null;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kSuchanfrage = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->kSuchanfrage;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_obj = Shop::DB()->executeQueryPrepared("
            SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                FROM tseo
                LEFT JOIN tsuchanfrage
                    ON tsuchanfrage.kSuchanfrage = tseo.kKey
                    AND tsuchanfrage.kSprache = tseo.kSprache
                WHERE cKey = 'kSuchanfrage' 
                    AND kKey = :key", ['key' => $this->getValue()], 1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($oSeo_obj->kSprache) && $language->kSprache === $oSeo_obj->kSprache) {
                $this->cSeo[$language->kSprache] = $oSeo_obj->cSeo;
            }
        }
        if (!empty($oSeo_obj->cSuche)) {
            $this->cName = $oSeo_obj->cSuche;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cSuche;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kSuchanfrage';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'tsuchanfrage';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return '';
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        $count           = 0;
        $kSucheCache_arr = [];
        $searchFilter    = $this->getNaviFilter()->getActiveState();
        if (is_array($searchFilter)) {
            $count = count($searchFilter);
            foreach ($searchFilter as $oSuchFilter) {
                if (isset($oSuchFilter->kSuchCache)) {
                    $kSucheCache_arr[] = (int)$oSuchFilter->kSuchCache;
                }
            }
        } elseif (isset($searchFilter->kSuchCache)) {
            $kSucheCache_arr[] = (int)$searchFilter->kSuchCache;
            $count             = 1;
        } elseif (($value = $searchFilter->getValue()) > 0) {
            $kSucheCache_arr = [$value];
            $count           = 1;
        }

        return (new FilterJoin())->setType('JOIN')
                                 ->setTable('(SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache, 
                                  MIN(tsuchcachetreffer.nSort) AS nSort
                                      FROM tsuchcachetreffer
                                      WHERE tsuchcachetreffer.kSuchCache IN (' . implode(',', $kSucheCache_arr) . ') 
                                      GROUP BY tsuchcachetreffer.kArtikel
                                      HAVING COUNT(*) = ' . $count . '
                                  ) AS jSuche')
                                 ->setOn('jSuche.kArtikel = tartikel.kArtikel')
                                 ->setComment('JOIN1 from FilterSearch');
    }

    /**
     * @param null $mixed
     * @return array|int|object
     */
    public function getOptions($mixed = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig()['navigationsfilter']['suchtrefferfilter_nutzen'] !== 'N') {
            $naviFilter = $this->getNaviFilter();
            $nLimit     = (isset($this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) &&
                ($limit = (int)$this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) > 0)
                ? " LIMIT " . $limit
                : '';
            $order      = $naviFilter->getOrder();
            $state      = $naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;
            $state->joins[] = (new FilterJoin())->setComment('join1 from getSearchFilterOptions')
                                                ->setType('JOIN')
                                                ->setTable('tsuchcachetreffer')
                                                ->setOn('tartikel.kArtikel = tsuchcachetreffer.kArtikel');
            $state->joins[] = (new FilterJoin())->setComment('join2 from getSearchFilterOptions')
                                                ->setType('JOIN')
                                                ->setTable('tsuchcache')
                                                ->setOn('tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache');
            $state->joins[] = (new FilterJoin())->setComment('join3 from getSearchFilterOptions')
                                                ->setType('JOIN')
                                                ->setTable('tsuchanfrage')
                                                ->setOn('tsuchanfrage.cSuche = tsuchcache.cSuche 
                                                            AND tsuchanfrage.kSprache = ' . $this->getLanguageID());

            $state->conditions[] = 'tsuchanfrage.nAktiv = 1';

            $query            = $naviFilter->getBaseQuery(
                ['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel'],
                $state->joins,
                $state->conditions,
                $state->having,
                $order->orderBy,
                '',
                ['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']
            );
            $query            = "SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, count(*) AS nAnzahl
                FROM (" . $query . ") AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche" . $nLimit;
            $searchFilters    = Shop::DB()->query($query, 2);
            $kSuchanfrage_arr = [];
            if ($naviFilter->Suche->kSuchanfrage > 0) {
                $kSuchanfrage_arr[] = (int)$naviFilter->Suche->kSuchanfrage;
            }
            if (count($naviFilter->SuchFilter) > 0) {
                foreach ($naviFilter->SuchFilter as $oSuchFilter) {
                    if ($oSuchFilter->getValue() > 0) {
                        $kSuchanfrage_arr[] = (int)$oSuchFilter->getValue();
                    }
                }
            }
            // entferne bereits gesetzte Filter aus dem Ergebnis-Array
            foreach ($searchFilters as $j => $searchFilter) {
                foreach ($kSuchanfrage_arr as $searchQuery) {
                    if ($searchFilter->kSuchanfrage === $searchQuery) {
                        unset($searchFilters[$j]);
                        break;
                    }
                }
            }
            if (is_array($searchFilters)) {
                $searchFilters = array_merge($searchFilters);
            }
            //baue URL
            $additionalFilter = new FilterBaseSearchQuery(
                $this->getLanguageID(),
                $this->getCustomerGroupID(),
                $this->getConfig(),
                $this->getAvailableLanguages()
            );
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($searchFilters);
            if ($nCount > 0) {
                $nPrioStep = ($searchFilters[0]->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) / 9;
            }
            foreach ($searchFilters as $searchFilter) {
                $class = rand(1, 10);
                if (isset($searchFilter->kSuchCache) && $searchFilter->kSuchCache > 0 && $nPrioStep >= 0) {
                    $class = round(
                            ($searchFilter->nAnzahl - $searchFilters[$nCount - 1]->nAnzahl) /
                            $nPrioStep
                        ) + 1;
                }
                $fe = (new FilterExtra())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setClass($class)
                    ->setParam($this->getUrlParam())
                    ->setName('@todo: setName for FilterSearch')
                    ->setValue((int)$searchFilter->kSuchanfrage)
                    ->setCount($searchFilter->nAnzahl)
                    ->setURL($naviFilter->getURL(
                        true,
                        $additionalFilter->init((int)$searchFilter->kSuchanfrage)
                    ));
                $fe->cSuche       = $searchFilter->cSuche;
                $fe->kSuchanfrage = $searchFilter->kSuchanfrage;

                $options[] = $fe;
            }
        }

        return $options;
    }
}
