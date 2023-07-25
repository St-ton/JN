<?php declare(strict_types=1);

namespace JTL\Filter\Items;

use JTL\Catalog\Category\Kategorie;
use JTL\Filter\FilterInterface;
use JTL\Filter\Join;
use JTL\Filter\Option;
use JTL\Filter\ProductFilter;
use JTL\Filter\States\BaseCategory;
use JTL\Filter\StateSQL;
use JTL\Filter\Type;
use JTL\Helpers\Category as CategoryHelper;
use JTL\Language\LanguageHelper;
use JTL\Shop;

/**
 * Class Category
 * @package JTL\Filter\Items
 */
class Category extends BaseCategory
{
    /**
     * Category constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
            ->setUrlParam('kf')
            ->setUrlParamSEO(\SEP_KAT)
            ->setVisibility($this->getConfig('navigationsfilter')['allgemein_kategoriefilter_benutzen'])
            ->setFrontendName(Shop::isAdmin() ? \__('filterCategory') : Shop::Lang()->get('allCategories'))
            ->setFilterName($this->getFrontendName())
            ->setType($this->getConfig('navigationsfilter')['category_filter_type'] === 'O'
                ? Type::OR
                : Type::AND);
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        parent::setSeo($languages);
        foreach ($this->slugs as $langID => $slug) {
            $this->cSeo[$langID] = $slug;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value): FilterInterface
    {
        $this->value = \is_array($value) ? \array_map('\intval', $value) : $value;

        return $this;
    }

    /**
     * @param array|int|string $value
     * @return $this
     */
    public function setValueCompat($value): FilterInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getValueCompat()
    {
        return \is_array($this->value) ? $this->value[0] : $this->value;
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        $value = $this->getValue();
        if (!\is_array($value)) {
            $value = [$value];
        }
        $values = ' IN (' . \implode(', ', $value) . ')';

        if ($this->getIncludeSubCategories() === true) {
            $row = $this->getConfig('navigationsfilter')['kategoriefilter_anzeigen_als'] === 'HF'
                ? 'tkategorieartikelgesamt'
                : 'tkategorieartikel';

            return $row . '.kKategorie IN (
                        SELECT tchild.kKategorie FROM tkategorie AS tparent
                            JOIN tkategorie AS tchild
                                ON tchild.lft BETWEEN tparent.lft AND tparent.rght
                                WHERE tparent.kKategorie ' . $values . ')';
        }

        return $this->getConfig('navigationsfilter')['kategoriefilter_anzeigen_als'] === 'HF'
            ? '(tkategorieartikelgesamt.kOberKategorie ' . $values .
            ' OR tkategorieartikelgesamt.kKategorie ' . $values . ') '
            : ' tkategorieartikel.kKategorie ' . $values;
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        $join = (new Join())
            ->setOrigin(__CLASS__ . '::getSQLJoin')
            ->setComment('join from ' . __METHOD__)
            ->setType('JOIN');
        if ($this->getConfig('navigationsfilter')['kategoriefilter_anzeigen_als'] === 'HF') {
            return $join->setTable('(
                SELECT tkategorieartikel.kArtikel, oberkategorie.kOberKategorie, oberkategorie.kKategorie
                    FROM tkategorieartikel
                        INNER JOIN tkategorie 
                            ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                        INNER JOIN tkategorie oberkategorie 
                            ON tkategorie.lft BETWEEN oberkategorie.lft 
                            AND oberkategorie.rght
                    ) tkategorieartikelgesamt')
                ->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel');
        }

        return $join->setTable('tkategorieartikel')
            ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel');
    }

    /**
     * @inheritdoc
     */
    public function getOptions($mixed = null): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        if ($this->getConfig('navigationsfilter')['allgemein_kategoriefilter_benutzen'] === 'N') {
            $this->options = [];

            return $this->options;
        }
        $categoryFilterType = $this->getConfig('navigationsfilter')['kategoriefilter_anzeigen_als'];
        $state              = $this->productFilter->getCurrentStateData(
            $this->getType() === Type::OR
                ? $this->getClassName()
                : null
        );
        $customerGroupID    = $this->getCustomerGroupID();
        $options            = [];
        $sql                = (new StateSQL())->from($state);
        // Kategoriefilter anzeige
        if ($categoryFilterType === 'HF' && !$this->productFilter->hasCategory()) {
            //@todo: $this instead of $naviFilter->KategorieFilter?
            $categoryIDFilter = $this->productFilter->hasCategoryFilter()
                ? ''
                : ' AND tkategorieartikelgesamt.kOberKategorie = 0';

            if (\count(\array_filter($sql->getJoins(), static function (Join $join) {
                return $join->getOrigin() === __CLASS__ . '::getSQLJoin';
            })) === 0) {
                $sql->addJoin((new Join())
                    ->setComment('join1 from ' . __METHOD__)
                    ->setType('JOIN')
                    ->setTable('(
                SELECT tkategorieartikel.kArtikel, oberkategorie.kOberKategorie, oberkategorie.kKategorie
                    FROM tkategorieartikel
                    INNER JOIN tkategorie
                        ON tkategorie.kKategorie = tkategorieartikel.kKategorie
                    INNER JOIN tkategorie oberkategorie
                        ON tkategorie.lft BETWEEN oberkategorie.lft
                        AND oberkategorie.rght
                    ) tkategorieartikelgesamt')
                    ->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel ' . $categoryIDFilter)
                    ->setOrigin(__CLASS__ . '::getOptions'));
            }
            $sql->addJoin((new Join())
                ->setComment('join2 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tkategorie')
                ->setOn('tkategorie.kKategorie = tkategorieartikelgesamt.kKategorie')
                ->setOrigin(__CLASS__));
        } else {
            if (!$this->productFilter->hasCategory()) {
                $sql->addJoin((new Join())
                    ->setComment('join3 from ' . __METHOD__)
                    ->setType('JOIN')
                    ->setTable('tkategorieartikel')
                    ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel')
                    ->setOrigin(__CLASS__));
            }
            $sql->addJoin((new Join())
                ->setComment('join4 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tkategorie')
                ->setOn('tkategorie.kKategorie = tkategorieartikel.kKategorie')
                ->setOrigin(__CLASS__));
        }
        if (!Shop::has('checkCategoryVisibility')) {
            Shop::set(
                'checkCategoryVisibility',
                $this->productFilter->getDB()->getAffectedRows('SELECT kKategorie FROM tkategoriesichtbarkeit') > 0
            );
        }
        if (Shop::get('checkCategoryVisibility')) {
            $sql->addJoin((new Join())
                ->setComment('join5 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tkategoriesichtbarkeit')
                ->setOn('tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = ' . $customerGroupID)
                ->setOrigin(__CLASS__));

            $sql->addCondition('tkategoriesichtbarkeit.kKategorie IS NULL');
        }
        $select = ['tkategorie.kKategorie', 'tkategorie.nSort'];
        if (LanguageHelper::isDefaultLanguageActive()) {
            $select[] = 'tkategorie.cName';
        } else {
            $select[] = "IF(tkategoriesprache.cName = '', tkategorie.cName, tkategoriesprache.cName) AS cName";
            $sql->addJoin((new Join())
                ->setComment('join5 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tkategoriesprache')
                ->setOn('tkategoriesprache.kKategorie = tkategorie.kKategorie 
                            AND tkategoriesprache.kSprache = ' . $this->getLanguageID())
                ->setOrigin(__CLASS__));
        }
        $sql->setSelect($select);
        $sql->setOrderBy(null);
        $sql->setLimit('');
        $sql->setGroupBy(['tkategorie.kKategorie', 'tartikel.kArtikel']);

        $baseQuery = $this->productFilter->getFilterSQL()->getBaseQuery($sql);
        $cacheID   = $this->getCacheID($baseQuery);
        if (($categories = $this->productFilter->getCache()->get($cacheID)) === false) {
            $db         = $this->productFilter->getDB();
            $categories = $db->getObjects(
                'SELECT tseo.cSeo AS slug, ssMerkmal.kKategorie AS id, ssMerkmal.cName AS name, 
                ssMerkmal.nSort AS sort, COUNT(*) AS cnt
                FROM (' . $baseQuery . " ) AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kKategorie
                        AND tseo.cKey = 'kKategorie'
                        AND tseo.kSprache = :lid
                    GROUP BY ssMerkmal.kKategorie
                    ORDER BY ssMerkmal.nSort, ssMerkmal.cName",
                ['lid' => $this->getLanguageID()]
            );
            foreach ($categories as $category) {
                $category->id   = (int)$category->id;
                $category->sort = (int)$category->sort;
                $category->cnt  = (int)$category->cnt;
            }
            if ($categoryFilterType === 'KP') { // category path
                $langID = $this->getLanguageID();
                $helper = CategoryHelper::getInstance($langID, $customerGroupID);
                foreach ($categories as $category) {
                    $category->name = $helper->getPath(new Kategorie(
                        $category->id,
                        $langID,
                        $customerGroupID,
                        false,
                        $db
                    ));
                }
            }
            $this->productFilter->getCache()->set($cacheID, $categories, [\CACHING_GROUP_FILTER]);
        }
        $additionalFilter   = new self($this->productFilter);
        $filterURLGenerator = $this->productFilter->getFilterURL();
        foreach ($categories as $category) {
            $options[] = (new Option())
                ->setIsActive($this->productFilter->filterOptionIsActive($this->getClassName(), $category->id))
                ->setParam($this->getUrlParam())
                ->setURL($filterURLGenerator->getURL($additionalFilter->init($category->id)))
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setName($category->name)
                ->setValue($category->id)
                ->setCount($category->cnt)
                ->setSort($category->sort);
        }
        if ($categoryFilterType === 'KP') {
            \usort($options, static function ($a, $b): int {
                /** @var Option $a */
                /** @var Option $b */
                return \strcmp($a->getName(), $b->getName());
            });
        }
        $this->options = $options;

        return $options;
    }
}
