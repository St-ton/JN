<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterBaseManufacturer
 */
class FilterBaseManufacturer extends AbstractFilter
{
    /**
     * @var int
     */
    public $kHersteller = 0;

    /**
     * FilterBaseManufacturer constructor.
     *
     * @param int|null   $languageID
     * @param int|null   $customerGroupID
     * @param array|null $config
     * @param array|null $languages
     */
    public function __construct($languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($languageID, $customerGroupID, $config, $languages);
        $this->isCustom    = false;
        $this->urlParam    = 'h';
        $this->urlParamSEO = SEP_HST;
    }

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
                    WHERE cKey = 'kHersteller' 
                        AND kKey = " . $this->getValue() . "
                    ORDER BY kSprache", 2
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (is_array($oSeo_arr)) {
                foreach ($oSeo_arr as $oSeo) {
                    $oSeo->kSprache = (int)$oSeo->kSprache;
                    if ($language->kSprache === $oSeo->kSprache) {
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
        $manufacturers = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_herstellerfilter_benutzen'] !== 'N') {
            //it's actually unnecessary to filter by manufacturer if we already got a manufacturer filter active...
//            if ($this->HerstellerFilter->isInitialized()) {
//                $filter              = new stdClass();
//                $filter->cSeo        = $this->HerstellerFilter->getSeo();
//                $filter->kHersteller = $this->HerstellerFilter->getValue();
//                $filter->cName       = $this->HerstellerFilter->getName();
//
//                return $filter;
//            }
            $naviFilter = Shop::getNaviFilter();
            $order      = $naviFilter->getOrder();
            $state      = $naviFilter->getCurrentStateData();
            $join       = new FilterJoin();
            $join->setComment('join from FilterManufacturer::getOptions()')
                 ->setType('JOIN')
                 ->setTable('thersteller')
                 ->setOn('tartikel.kHersteller = thersteller.kHersteller');

            $state->joins[] = $order->join;
            $state->joins[] = $join;

            $query = $naviFilter->getBaseQuery([
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
                    LEFT JOIN tseo 
                        ON tseo.kKey = ssMerkmal.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = " . $this->getLanguageID() . "
                    GROUP BY ssMerkmal.kHersteller
                    ORDER BY ssMerkmal.nSortNr, ssMerkmal.cName";

            $manufacturers    = Shop::DB()->query($query, 2);
            $additionalFilter = new FilterItemManufacturer(
                $this->getLanguageID(),
                $this->getCustomerGroupID(),
                $this->getConfig(),
                $this->getAvailableLanguages()
            );

            foreach ($manufacturers as $manufacturer) {
                $manufacturer->kHersteller = (int)$manufacturer->kHersteller;
                $manufacturer->nAnzahl     = (int)$manufacturer->nAnzahl;
                $manufacturer->nSortNr     = (int)$manufacturer->nSortNr;
                $manufacturer->cURL        = $naviFilter->getURL(
                    true,
                    $additionalFilter->init((int)$manufacturer->kHersteller)
                );
            }
        }

        return $manufacturers;
    }
}
