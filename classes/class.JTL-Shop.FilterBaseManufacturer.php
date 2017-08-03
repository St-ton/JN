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
     * @param Navigationsfilter $naviFilter
     */
    public function __construct($naviFilter)
    {
        parent::__construct($naviFilter);
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
        $oSeo_arr = Shop::DB()->query(
                "SELECT tseo.cSeo, tseo.kSprache, thersteller.cName
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
            // invalid manufacturer ID
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
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_herstellerfilter_benutzen'] !== 'N') {
            $order      = $this->naviFilter->getOrder();
            $state      = $this->naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;
            $state->joins[] = (new FilterJoin())
                ->setComment('join from FilterManufacturer::getOptions()')
                ->setType('JOIN')
                ->setTable('thersteller')
                ->setOn('tartikel.kHersteller = thersteller.kHersteller')
                ->setOrigin(__CLASS__);

            $query = $this->naviFilter->getBaseQuery(
                [
                    'thersteller.kHersteller',
                    'thersteller.cName',
                    'thersteller.nSortNr',
                    'tartikel.kArtikel'
                ],
                $state->joins,
                $state->conditions,
                $state->having,
                $order->orderBy
            );
            $query = "SELECT tseo.cSeo, ssMerkmal.kHersteller, ssMerkmal.cName, ssMerkmal.nSortNr, COUNT(*) AS nAnzahl
                FROM (" .
                    $query .
                ") AS ssMerkmal
                    LEFT JOIN tseo 
                        ON tseo.kKey = ssMerkmal.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = " . $this->getLanguageID() . "
                    GROUP BY ssMerkmal.kHersteller
                    ORDER BY ssMerkmal.nSortNr, ssMerkmal.cName";

            $manufacturers    = Shop::DB()->query($query, 2);
            $additionalFilter = new FilterItemManufacturer($this->naviFilter);

            foreach ($manufacturers as $manufacturer) {
                // attributes for old filter templates
                $manufacturer->kHersteller = (int)$manufacturer->kHersteller;
                $manufacturer->nAnzahl     = (int)$manufacturer->nAnzahl;
                $manufacturer->nSortNr     = (int)$manufacturer->nSortNr;
                $manufacturer->cURL        = $this->naviFilter->getURL(
                    true,
                    $additionalFilter->init((int)$manufacturer->kHersteller)
                );

                $fe              = (new FilterExtra())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName($manufacturer->cName)
                    ->setValue((int)$manufacturer->kHersteller)
                    ->setCount($manufacturer->nAnzahl)
                    ->setSort($manufacturer->nSortNr)
                    ->setURL($this->naviFilter->getURL(
                        true,
                        $additionalFilter->init((int)$manufacturer->kHersteller)
                    ));
                $fe->kHersteller = (int)$manufacturer->kHersteller;

                $options[] = $fe;
            }
        }
        $this->options = $options;

        return $options;
    }
}
