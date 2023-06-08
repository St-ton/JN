<?php declare(strict_types=1);

namespace JTL\Filter\States;

use JTL\Catalog\Hersteller;
use JTL\Filter\AbstractFilter;
use JTL\Filter\FilterInterface;
use JTL\Filter\Items\Manufacturer;
use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\StateSQL;
use JTL\Filter\Type;
use JTL\MagicCompatibilityTrait;
use JTL\Media\Image;
use JTL\Router\RoutableTrait;
use JTL\Router\Router;
use JTL\Shop;

/**
 * Class BaseManufacturer
 * @package JTL\Filter\States
 */
class BaseManufacturer extends AbstractFilter
{
    use MagicCompatibilityTrait;
    use RoutableTrait;

    /**
     * @var array
     */
    public static array $mapping = [
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
        $this->setRouteType(Router::TYPE_MANUFACTURER);
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
            $seoData = $this->productFilter->getDB()->getObjects(
                "SELECT tseo.cSeo, tseo.kSprache, thersteller.cName
                    FROM tseo
                    JOIN thersteller
                        ON thersteller.kHersteller = tseo.kKey
                        AND thersteller.nAktiv = 1
                    WHERE cKey = 'kHersteller' 
                        AND kKey IN (" . \implode(', ', \array_map('\intval', $val)) . ')'
            );
            foreach ($languages as $language) {
                $langID              = $language->kSprache;
                $this->cSeo[$langID] = '';
                foreach ($seoData as $seo) {
                    if ($langID === (int)$seo->kSprache) {
                        $sep                  = $this->cSeo[$langID] === '' ? '' : \SEP_HST;
                        $this->cSeo[$langID] .= $sep . $seo->cSeo;
                        $this->slugs[$langID] = $seo->cSeo;
                    }
                }
            }
            $this->createBySlug();
            foreach ($this->getURLPaths() as $langID => $slug) {
                $this->cSeo[$langID] = \ltrim($slug, '/');
            }
            if (isset($seoData[0]->cName)) {
                $this->setName($seoData[0]->cName);
            } else {
                // invalid manufacturer ID
                Shop::getState()->manufacturerID = 0;
                Shop::getState()->is404          = true;
                Shop::$kHersteller               = 0;
                Shop::$is404                     = true;
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRoute(array $additional): ?string
    {
        $currentLanguageID = $this->getLanguageID();
        $manufacturer      = new Hersteller($this->getValue(), $currentLanguageID);
        $manufacturer->createBySlug($this->getValue(), $additional);

        return \ltrim($manufacturer->getURLPath($currentLanguageID), '/');
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
            : \implode(' AND ', \array_map(function ($e): string {
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
     * @inheritdoc
     */
    public function getOptions($mixed = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig('navigationsfilter')['allgemein_herstellerfilter_benutzen'] === 'N') {
            return $options;
        }
        $state = $this->productFilter->getCurrentStateData(
            $this->getType() === Type::OR
                ? $this->getClassName()
                : null
        );
        $sql   = (new StateSQL())->from($state);
        $sql->setSelect([
            'thersteller.kHersteller',
            'thersteller.cName',
            'thersteller.nSortNr',
            'thersteller.cBildPfad',
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
        $cacheID   = $this->getCacheID($baseQuery);
        if (($manufacturers = $this->productFilter->getCache()->get($cacheID)) === false) {
            $manufacturers = $this->productFilter->getDB()->getObjects(
                'SELECT tseo.cSeo, ssMerkmal.kHersteller AS id, ssMerkmal.cName AS name,
                ssMerkmal.nSortNr AS sort, ssMerkmal.cBildPfad, COUNT(*) AS cnt
                FROM (' . $baseQuery . ") AS ssMerkmal
                    LEFT JOIN tseo 
                        ON tseo.kKey = ssMerkmal.kHersteller
                        AND tseo.cKey = 'kHersteller'
                        AND tseo.kSprache = :lid
                    GROUP BY ssMerkmal.kHersteller
                    ORDER BY ssMerkmal.nSortNr, ssMerkmal.cName",
                ['lid' => $this->getLanguageID()]
            );
            foreach ($manufacturers as $manufacturer) {
                $manufacturer->id   = (int)$manufacturer->id;
                $manufacturer->cnt  = (int)$manufacturer->cnt;
                $manufacturer->sort = (int)$manufacturer->sort;
            }
            $this->productFilter->getCache()->set($cacheID, $manufacturers, [\CACHING_GROUP_FILTER]);
        }
        $additionalFilter = new Manufacturer($this->productFilter);
        foreach ($manufacturers as $manufacturer) {
            // attributes for old filter templates
            $manufacturer->url = $this->productFilter->getFilterURL()->getURL(
                $additionalFilter->init($manufacturer->id)
            );
            $manufacturerData  = new Hersteller($manufacturer->id, $this->getLanguageID());

            $options[] = (new Option())
                ->setURL($manufacturer->url)
                ->setIsActive(
                    $this->productFilter->filterOptionIsActive(
                        $this->getClassName(),
                        $manufacturer->id
                    )
                )
                ->setType($this->getType())
                ->setFrontendName($manufacturer->name)
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($manufacturer->name)
                ->setValue($manufacturer->id)
                ->setCount($manufacturer->cnt)
                ->setSort($manufacturer->sort)
                ->setData('cBildpfadKlein', $manufacturerData->getImage(Image::SIZE_XS));
        }
        $this->options = $options;

        return $options;
    }
}
