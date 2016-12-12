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
}
