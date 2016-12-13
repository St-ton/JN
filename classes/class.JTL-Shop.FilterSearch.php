<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterSearch
 */
class FilterSearch extends AbstractFilter implements IFilter
{
    /**
     * @var bool
     */
    public $isCustom = false;

    /**
     * @var int
     */
    public $kSuchanfrage = 0;

    /**
     * @var string
     */
    public $cSuche;

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
        $oSeo_obj = Shop::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, tsuchanfrage.cSuche
                    FROM tseo
                    LEFT JOIN tsuchanfrage
                        ON tsuchanfrage.kSuchanfrage = tseo.kKey
                        AND tsuchanfrage.kSprache = tseo.kSprache
                    WHERE cKey = 'kSuchanfrage' AND kKey = " . $this->getValue(), 1
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (isset($oSeo_obj->kSprache) && $language->kSprache == $oSeo_obj->kSprache) {
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
        $searchFilter    = Shop::getNaviFilter()->getActiveState();
        if (is_array($searchFilter)) {
            $count = count($searchFilter);
            foreach ($searchFilter as $oSuchFilter) {
                if (isset($oSuchFilter->kSuchCache)) {
                    $kSucheCache_arr[] = (int)$oSuchFilter->kSuchCache;
                }
            }
        } elseif (isset($searchFilter->kSuchCache)) {
            $kSucheCache_arr[] = (int)$searchFilter->kSuchCache;
            $count = 1;
        }
        $join = new FilterJoin();
        $join->setType('JOIN')
             ->setTable('(
                            SELECT tsuchcachetreffer.kArtikel, tsuchcachetreffer.kSuchCache, MIN(tsuchcachetreffer.nSort) AS nSort
                            FROM tsuchcachetreffer
                                WHERE tsuchcachetreffer.kSuchCache IN (' . implode(',', $kSucheCache_arr) . ') GROUP BY tsuchcachetreffer.kArtikel
                                HAVING COUNT(*) = ' . $count . '
                                ) AS jSuche')
             ->setOn('jSuche.kArtikel = tartikel.kArtikel')
             ->setComment('JOIN1 from FilterSearch');

        return $join;
    }

    /**
     * @param null $mixed
     * @return array|int|object
     */
    public function getOptions($mixed = null)
    {
        $oSuchFilterDB_arr = [];
        if ($this->getConfig()['navigationsfilter']['suchtrefferfilter_nutzen'] !== 'N') {
            $naviFilter = Shop::getNaviFilter();
            $nLimit     = (isset($this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) && ($limit = (int)$this->getConfig()['navigationsfilter']['suchtrefferfilter_anzahl']) > 0)
                ? " LIMIT " . $limit
                : '';
            $order      = $naviFilter->getOrder();
            $state      = $naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;
            $join           = new FilterJoin();
            $join->setComment('join1 from getSearchFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tsuchcachetreffer')
                 ->setOn('tartikel.kArtikel = tsuchcachetreffer.kArtikel');
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join2 from getSearchFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tsuchcache')
                 ->setOn('tsuchcache.kSuchCache = tsuchcachetreffer.kSuchCache');
            $state->joins[] = $join;

            $join = new FilterJoin();
            $join->setComment('join3 from getSearchFilterOptions')
                 ->setType('JOIN')
                 ->setTable('tsuchanfrage')
                 ->setOn('tsuchanfrage.cSuche = tsuchcache.cSuche AND tsuchanfrage.kSprache = ' . $this->getLanguageID());
            $state->joins[] = $join;

            $state->conditions[] = "tsuchanfrage.nAktiv = 1";

            $query = $naviFilter->getBaseQuery(['tsuchanfrage.kSuchanfrage', 'tsuchanfrage.cSuche', 'tartikel.kArtikel'],
                $state->joins, $state->conditions, $state->having, $order->orderBy, '',
                ['tsuchanfrage.kSuchanfrage', 'tartikel.kArtikel']);

            $query = "SELECT ssMerkmal.kSuchanfrage, ssMerkmal.cSuche, count(*) AS nAnzahl
                FROM (" . $query . ") AS ssMerkmal
                    GROUP BY ssMerkmal.kSuchanfrage
                    ORDER BY ssMerkmal.cSuche" . $nLimit;

            $oSuchFilterDB_arr = Shop::DB()->query($query, 2);

            $kSuchanfrage_arr = [];
            if ($naviFilter->Suche->kSuchanfrage > 0) {
                $kSuchanfrage_arr[] = (int)$naviFilter->Suche->kSuchanfrage;
            }
            if (count($naviFilter->SuchFilter) > 0) {
                foreach ($naviFilter->SuchFilter as $oSuchFilter) {
                    if (isset($oSuchFilter->kSuchanfrage)) {
                        $kSuchanfrage_arr[] = (int)$oSuchFilter->kSuchanfrage;
                    }
                }
            }
            // Werfe bereits gesetzte Filter aus dem Ergebnis Array
            $nCount = count($oSuchFilterDB_arr);
            $count  = count($kSuchanfrage_arr);
            for ($j = 0; $j < $nCount; ++$j) {
                for ($i = 0; $i < $count; ++$i) {
                    if ($oSuchFilterDB_arr[$j]->kSuchanfrage == $kSuchanfrage_arr[$i]) {
                        unset($oSuchFilterDB_arr[$j]);
                        break;
                    }
                }
            }
            if (is_array($oSuchFilterDB_arr)) {
                $oSuchFilterDB_arr = array_merge($oSuchFilterDB_arr);
            }
            //baue URL
            $count = count($oSuchFilterDB_arr);
            for ($i = 0; $i < $count; ++$i) {
                $oZusatzFilter                           = new stdClass();
                $oZusatzFilter->SuchFilter               = new stdClass();
                $oZusatzFilter->SuchFilter->kSuchanfrage = (int)$oSuchFilterDB_arr[$i]->kSuchanfrage;
                $oSuchFilterDB_arr[$i]->cURL             = $naviFilter->getURL(true, $oZusatzFilter);
            }
            // Priorität berechnen
            $nPrioStep = 0;
            $nCount    = count($oSuchFilterDB_arr);
            if ($nCount > 0) {
                $nPrioStep = ($oSuchFilterDB_arr[0]->nAnzahl - $oSuchFilterDB_arr[$nCount - 1]->nAnzahl) / 9;
            }
            foreach ($oSuchFilterDB_arr as $i => $oSuchFilterDB) {
                $oSuchFilterDB_arr[$i]->Klasse = rand(1, 10);
                if (isset($oSuchFilterDB->kSuchCache) && $oSuchFilterDB->kSuchCache > 0 && $nPrioStep >= 0) {
                    $oSuchFilterDB_arr[$i]->Klasse = round(($oSuchFilterDB->nAnzahl - $oSuchFilterDB_arr[$nCount - 1]->nAnzahl) / $nPrioStep) + 1;
                }
            }
        }

        return $oSuchFilterDB_arr;
    }
}
