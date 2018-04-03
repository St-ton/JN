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
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('h')
             ->setUrlParamSEO(SEP_HST);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value)
    {
        return parent::setValue((int)$value);
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $val = $this->getValue();
        if ((is_numeric($val) && $val > 0) || (is_array($val) && count($val) > 0)) {
            if (!is_array($val)) {
                $val = [$val];
            }
            $oSeo_arr = Shop::Container()->getDB()->query(
                "SELECT tseo.cSeo, tseo.kSprache, thersteller.cName
                    FROM tseo
                        JOIN thersteller
                            ON thersteller.kHersteller = tseo.kKey
                    WHERE cKey = 'kHersteller' 
                        AND kKey IN (" . implode(', ', $val). ")
                    ORDER BY kSprache",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($languages as $language) {
                $this->cSeo[$language->kSprache] = '';
                foreach ($oSeo_arr as $oSeo) {
                    if ($language->kSprache === (int)$oSeo->kSprache) {
                        $sep = $this->cSeo[$language->kSprache] === '' ? '' : SEP_HST;
                        $this->cSeo[$language->kSprache] .= $sep . $oSeo->cSeo;
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
        $val = $this->getValue();
        if (!is_array($val)) {
            $val = [$val];
        }
        return $this->getType() === AbstractFilter::FILTER_TYPE_OR
            ? 'tartikel.' . $this->getPrimaryKeyRow() . ' IN (' . implode(', ', $val) . ')'
            : implode(' AND ', array_map(function($e) {
                return 'tartikel.' . $this->getPrimaryKeyRow() . ' = ' . $e;
            }, $val));
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
     * @return FilterOption[]
     */
    public function getOptions($data = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_herstellerfilter_benutzen'] !== 'N') {
            $state = $this->productFilter->getCurrentStateData($this->getType() === AbstractFilter::FILTER_TYPE_OR
                ? $this->getClassName()
                : null
            );

            $state->joins[] = (new FilterJoin())
                ->setComment('JOIN from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('thersteller')
                ->setOn('tartikel.kHersteller = thersteller.kHersteller')
                ->setOrigin(__CLASS__);

            $query = $this->productFilter->getFilterSQL()->getBaseQuery(
                [
                    'thersteller.kHersteller',
                    'thersteller.cName',
                    'thersteller.nSortNr',
                    'tartikel.kArtikel'
                ],
                $state->joins,
                $state->conditions,
                $state->having
            );
            $manufacturers    = Shop::Container()->getDB()->query(
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
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $additionalFilter = new FilterItemManufacturer($this->productFilter);
            foreach ($manufacturers as $manufacturer) {
                // attributes for old filter templates
                $manufacturer->kHersteller = (int)$manufacturer->kHersteller;
                $manufacturer->nAnzahl     = (int)$manufacturer->nAnzahl;
                $manufacturer->nSortNr     = (int)$manufacturer->nSortNr;
                $manufacturer->cURL        = $this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init($manufacturer->kHersteller)
                );

                $options[] = (new FilterOption())
                    ->setType($this->getType())
                    ->setFrontendName($manufacturer->cName)
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName($manufacturer->cName)
                    ->setValue($manufacturer->kHersteller)
                    ->setCount($manufacturer->nAnzahl)
                    ->setSort($manufacturer->nSortNr)
                    ->setURL($manufacturer->cURL)
                    ->setIsActive($this->productFilter->filterOptionIsActive(
                        $this->getClassName(),
                        $manufacturer->kHersteller)
                    );
            }
        }
        $this->options = $options;

        return $options;
    }
}
