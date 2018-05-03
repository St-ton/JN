<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\States;

use DB\ReturnType;
use Filter\AbstractFilter;
use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\FilterInterface;
use Filter\Items\ItemManufacturer;
use Filter\ProductFilter;

/**
 * Class BaseManufacturer
 */
class BaseManufacturer extends AbstractFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'kHersteller' => 'ValueCompat',
        'cName'       => 'Name'
    ];

    /**
     * BaseManufacturer constructor.
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
    public function setValue($value): FilterInterface
    {
        return parent::setValue((int)$value);
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $val = $this->getValue();
        if ((is_numeric($val) && $val > 0) || (is_array($val) && count($val) > 0)) {
            if (!is_array($val)) {
                $val = [$val];
            }
            $oSeo_arr = \Shop::Container()->getDB()->query(
                "SELECT tseo.cSeo, tseo.kSprache, thersteller.cName
                    FROM tseo
                    JOIN thersteller
                        ON thersteller.kHersteller = tseo.kKey
                    WHERE cKey = 'kHersteller' 
                        AND kKey IN (" . implode(', ', $val) . ")",
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($languages as $language) {
                $this->cSeo[$language->kSprache] = '';
                foreach ($oSeo_arr as $oSeo) {
                    if ($language->kSprache === (int)$oSeo->kSprache) {
                        $sep                             = $this->cSeo[$language->kSprache] === '' ? '' : SEP_HST;
                        $this->cSeo[$language->kSprache] .= $sep . $oSeo->cSeo;
                    }
                }
            }
            if (isset($oSeo_arr[0]->cName)) {
                $this->setName($oSeo_arr[0]->cName);
            } else {
                // invalid manufacturer ID
                \Shop::$kHersteller = 0;
                \Shop::$is404       = true;
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kHersteller';
    }

    /**
     * @inheritdoc
     */
    public function getTableName(): string
    {
        return 'thersteller';
    }

    /**
     * @return string
     */
    public function getSQLCondition(): string
    {
        $val = $this->getValue();
        if (!is_array($val)) {
            $val = [$val];
        }

        return $this->getType() === AbstractFilter::FILTER_TYPE_OR
            ? 'tartikel.' . $this->getPrimaryKeyRow() . ' IN (' . implode(', ', $val) . ')'
            : implode(' AND ', array_map(function ($e) {
                return 'tartikel.' . $this->getPrimaryKeyRow() . ' = ' . $e;
            }, $val));
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        return [];
    }

    /**
     * @param null $data
     * @return FilterOption[]
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_herstellerfilter_benutzen'] === 'N') {
            return $options;
        }
        $state = $this->productFilter->getCurrentStateData($this->getType() === AbstractFilter::FILTER_TYPE_OR
            ? $this->getClassName()
            : null
        );

        $state->addJoin((new FilterJoin())
            ->setComment('JOIN from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('thersteller')
            ->setOn('tartikel.kHersteller = thersteller.kHersteller')
            ->setOrigin(__CLASS__));

        $query            = $this->productFilter->getFilterSQL()->getBaseQuery(
            [
                'thersteller.kHersteller',
                'thersteller.cName',
                'thersteller.nSortNr',
                'tartikel.kArtikel'
            ],
            $state->getJoins(),
            $state->getConditions(),
            $state->getHaving()
        );
        $manufacturers    = \Shop::Container()->getDB()->query(
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
            ReturnType::ARRAY_OF_OBJECTS
        );
        $additionalFilter = new ItemManufacturer($this->productFilter);
        foreach ($manufacturers as $manufacturer) {
            // attributes for old filter templates
            $manufacturer->kHersteller = (int)$manufacturer->kHersteller;
            $manufacturer->nAnzahl     = (int)$manufacturer->nAnzahl;
            $manufacturer->nSortNr     = (int)$manufacturer->nSortNr;
            $manufacturer->cURL        = $this->productFilter->getFilterURL()->getURL(
                $additionalFilter->init($manufacturer->kHersteller)
            );

            $options[] = (new FilterOption())
                ->setURL($manufacturer->cURL)
                ->setIsActive($this->productFilter->filterOptionIsActive(
                    $this->getClassName(),
                    $manufacturer->kHersteller)
                )
                ->setType($this->getType())
                ->setFrontendName($manufacturer->cName)
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($manufacturer->cName)
                ->setValue($manufacturer->kHersteller)
                ->setCount($manufacturer->nAnzahl)
                ->setSort($manufacturer->nSortNr);
        }
        $this->options = $options;

        return $options;
    }
}
