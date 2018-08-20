<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\States;


use DB\ReturnType;
use Filter\AbstractFilter;
use Filter\FilterInterface;
use Filter\Items\Manufacturer;
use Filter\Join;
use Filter\Option;
use Filter\ProductFilter;
use Filter\StateSQL;
use Filter\Type;

/**
 * Class BaseManufacturer
 */
class BaseManufacturer extends AbstractFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
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
             ->setUrlParamSEO(\SEP_HST);
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
        if ((\is_numeric($val) && $val > 0) || (\is_array($val) && \count($val) > 0)) {
            if (!\is_array($val)) {
                $val = [$val];
            }
            $oSeo_arr = $this->productFilter->getDB()->query(
                "SELECT tseo.cSeo, tseo.kSprache, thersteller.cName
                    FROM tseo
                    JOIN thersteller
                        ON thersteller.kHersteller = tseo.kKey
                    WHERE cKey = 'kHersteller' 
                        AND kKey IN (" . \implode(', ', $val) . ")",
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($languages as $language) {
                $this->cSeo[$language->kSprache] = '';
                foreach ($oSeo_arr as $oSeo) {
                    if ($language->kSprache === (int)$oSeo->kSprache) {
                        $sep                             = $this->cSeo[$language->kSprache] === '' ? '' : \SEP_HST;
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
        if (!\is_array($val)) {
            $val = [$val];
        }

        return $this->getType() === Type::OR
            ? 'tartikel.' . $this->getPrimaryKeyRow() . ' IN (' . \implode(', ', $val) . ')'
            : \implode(' AND ', \array_map(function ($e) {
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
     * @return Option[]
     */
    public function getOptions($data = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig('navigationsfilter')['allgemein_herstellerfilter_benutzen'] === 'N') {
            return $options;
        }
        $state = $this->productFilter->getCurrentStateData($this->getType() === Type::OR
            ? $this->getClassName()
            : null
        );
        $sql   = (new StateSQL())->from($state);
        $sql->setSelect([
            'thersteller.kHersteller',
            'thersteller.cName',
            'thersteller.nSortNr',
            'tartikel.kArtikel'
        ]);
        $sql->setOrderBy(null);
        $sql->setLimit('');
        $sql->setGroupBy(['tartikel.kArtikel']);
        $sql->addJoin((new Join())
            ->setComment('JOIN from ' . __METHOD__)
            ->setType('JOIN')
            ->setTable('thersteller')
            ->setOn('tartikel.kHersteller = thersteller.kHersteller')
            ->setOrigin(__CLASS__));
        $baseQuery = $this->productFilter->getFilterSQL()->getBaseQuery($sql);
        $cacheID   = 'fltr_' . __CLASS__ . \md5($baseQuery);
        if (($cached = $this->productFilter->getCache()->get($cacheID)) !== false) {
            $this->options = $cached;

            return $this->options;
        }
        $manufacturers    = $this->productFilter->getDB()->query(
            "SELECT tseo.cSeo, ssMerkmal.kHersteller, ssMerkmal.cName, ssMerkmal.nSortNr, COUNT(*) AS nAnzahl
                FROM (" . $baseQuery . ") AS ssMerkmal
                    LEFT JOIN tseo 
                        ON tseo.kKey = ssMerkmal.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = " . $this->getLanguageID() . "
                    GROUP BY ssMerkmal.kHersteller
                    ORDER BY ssMerkmal.nSortNr, ssMerkmal.cName",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $additionalFilter = new Manufacturer($this->productFilter);
        foreach ($manufacturers as $manufacturer) {
            // attributes for old filter templates
            $manufacturer->kHersteller = (int)$manufacturer->kHersteller;
            $manufacturer->nAnzahl     = (int)$manufacturer->nAnzahl;
            $manufacturer->nSortNr     = (int)$manufacturer->nSortNr;
            $manufacturer->cURL        = $this->productFilter->getFilterURL()->getURL(
                $additionalFilter->init($manufacturer->kHersteller)
            );

            $options[] = (new Option())
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
        $this->productFilter->getCache()->set($cacheID, $options, [CACHING_GROUP_FILTER]);

        return $options;
    }
}
