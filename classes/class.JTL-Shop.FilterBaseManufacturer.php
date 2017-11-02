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
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'kHersteller' => 'ValueCompat',
        'cName'       => 'Name'
    ];

    /**
     * FilterBaseManufacturer constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct($productFilter)
    {
        parent::__construct($productFilter);
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
        $this->value = (int)$id;

        return $this;
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
                ORDER BY kSprache",
            2
        );
        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            foreach ($oSeo_arr as $oSeo) {
                if ($language->kSprache === (int)$oSeo->kSprache) {
                    $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                }
            }
        }
        if (isset($oSeo_arr[0]->cName)) {
            $this->setName($oSeo_arr[0]->cName);
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
     * @param null $data
     * @return array|int|object
     */
    public function getOptions($data = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_herstellerfilter_benutzen'] !== 'N') {
            $order      = $this->productFilter->getOrder();
            $state      = $this->productFilter->getCurrentStateData();

            $state->joins[] = $order->join;
            $state->joins[] = (new FilterJoin())
                ->setComment('join from FilterManufacturer::getOptions()')
                ->setType('JOIN')
                ->setTable('thersteller')
                ->setOn('tartikel.kHersteller = thersteller.kHersteller')
                ->setOrigin(__CLASS__);

            $query = $this->productFilter->getBaseQuery(
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
            $manufacturers    = Shop::DB()->query(
                "SELECT tseo.cSeo, ssMerkmal.kHersteller, ssMerkmal.cName, ssMerkmal.nSortNr, COUNT(*) AS nAnzahl
                    FROM (" .
                    $query .
                    ") AS ssMerkmal
                        LEFT JOIN tseo 
                            ON tseo.kKey = ssMerkmal.kHersteller
                            AND tseo.cKey = 'kHersteller'
                            AND tseo.kSprache = " . $this->getLanguageID() . "
                        GROUP BY ssMerkmal.kHersteller
                        ORDER BY ssMerkmal.nSortNr, ssMerkmal.cName",
                2
            );
            $additionalFilter = new FilterItemManufacturer($this->productFilter);
            foreach ($manufacturers as $manufacturer) {
                // attributes for old filter templates
                $manufacturer->kHersteller = (int)$manufacturer->kHersteller;
                $manufacturer->nAnzahl     = (int)$manufacturer->nAnzahl;
                $manufacturer->nSortNr     = (int)$manufacturer->nSortNr;
                $manufacturer->cURL        = $this->productFilter->getURL(
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
                    ->setURL($this->productFilter->getURL(
                        $additionalFilter->init((int)$manufacturer->kHersteller)
                    ));

                $options[] = $fe;
            }
        }
        $this->options = $options;

        return $options;
    }
}
