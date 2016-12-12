<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterHersteller
 */
class FilterHersteller extends AbstractFilter implements IFilter
{
    /**
     * @var bool
     */
    public $isCustom = false;

    /**
     * @var int
     */
    public $kHersteller = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kHersteller = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->kHersteller;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_arr = Shop::DB()->query("
                SELECT tseo.cSeo, tseo.kSprache, thersteller.cName
                    FROM tseo
                        LEFT JOIN thersteller
                        ON thersteller.kHersteller = tseo.kKey
                    WHERE cKey = 'kHersteller' AND kKey = " . $this->getValue() . "
                    ORDER BY kSprache", 2
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (is_array($oSeo_arr)) {
                foreach ($oSeo_arr as $oSeo) {
                    if ($language->kSprache == $oSeo->kSprache) {
                        $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                    }
                }
            }
        }
        if (isset($oSeo_arr[0]->cName)) {
            $this->cName = $oSeo_arr[0]->cName;
        } else {
            //invalid manufacturer ID
            Shop::$kHersteller = 0;
            Shop::$is404       = true;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kHersteller';
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'thersteller';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        return 'tartikel.' . $this->getPrimaryKeyRow() . ' = ' . $this->getValue();
    }

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin()
    {
        return [];
    }

    /**
     * @param null $mixed
     * @return array|int|object
     */
    public function getOptions($mixed = null)
    {
        $oHerstellerFilterDB_arr = [];
        if ($this->navifilter->getConfig()['navigationsfilter']['allgemein_herstellerfilter_benutzen'] !== 'N') {
            //it's actually stupid to filter by manufacturer if we already got a manufacturer filter active...
//            if ($this->HerstellerFilter->isInitialized()) {
//                $filter              = new stdClass();
//                $filter->cSeo        = $this->HerstellerFilter->getSeo();
//                $filter->kHersteller = $this->HerstellerFilter->getValue();
//                $filter->cName       = $this->HerstellerFilter->getName();
//
//                return $filter;
//            }
            $order = $this->navifilter->getOrder();
            $state = $this->navifilter->getCurrentStateData();
            $join  = new FilterJoin();
            $join->setComment('join from FilterHersteller::getOptions()')
                 ->setType('JOIN')
                 ->setTable('thersteller')
                 ->setOn('tartikel.kHersteller = thersteller.kHersteller');

            $state->joins[] = $order->join;
            $state->joins[] = $join;

            $query = $this->navifilter->getBaseQuery([
                'thersteller.kHersteller',
                'thersteller.cName',
                'thersteller.nSortNr',
                'tartikel.kArtikel'
            ], $state->joins, $state->conditions, $state->having, $order->orderBy);
            $query = "
            SELECT tseo.cSeo, ssMerkmal.kHersteller, ssMerkmal.cName, ssMerkmal.nSortNr, COUNT(*) AS nAnzahl
                FROM
                (" . $query . "
                ) AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = " . $this->navifilter->getLanguageID() . "
                    GROUP BY ssMerkmal.kHersteller
                    ORDER BY ssMerkmal.nSortNr, ssMerkmal.cName";

            $oHerstellerFilterDB_arr = Shop::DB()->query($query, 2);
            //baue URL
            $oZusatzFilter = new stdClass();
            $count         = count($oHerstellerFilterDB_arr);
            for ($i = 0; $i < $count; ++$i) {
                $oHerstellerFilterDB_arr[$i]->kHersteller = (int)$oHerstellerFilterDB_arr[$i]->kHersteller;
                $oHerstellerFilterDB_arr[$i]->nAnzahl     = (int)$oHerstellerFilterDB_arr[$i]->nAnzahl;
                $oHerstellerFilterDB_arr[$i]->nSortNr     = (int)$oHerstellerFilterDB_arr[$i]->nSortNr;

                $oZusatzFilter->HerstellerFilter              = new stdClass();
                $oZusatzFilter->HerstellerFilter->kHersteller = (int)$oHerstellerFilterDB_arr[$i]->kHersteller;
                $oZusatzFilter->HerstellerFilter->cSeo        = $oHerstellerFilterDB_arr[$i]->cSeo;

                $oHerstellerFilterDB_arr[$i]->cURL = $this->navifilter->getURL(true, $oZusatzFilter);
            }
            unset($oZusatzFilter);
        }

        return $oHerstellerFilterDB_arr;
    }
}
