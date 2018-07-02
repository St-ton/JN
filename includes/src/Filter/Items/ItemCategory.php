<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\Items;

use DB\ReturnType;
use Filter\FilterJoin;
use Filter\FilterOption;
use Filter\FilterStateSQL;
use Filter\Type;
use Filter\ProductFilter;
use Filter\States\BaseCategory;

/**
 * Class ItemCategory
 * @package Filter\Items
 */
class ItemCategory extends BaseCategory
{
    /**
     * ItemCategory constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('kf')
             ->setUrlParamSEO(SEP_KAT)
             ->setVisibility($this->getConfig('navigationsfilter')['allgemein_kategoriefilter_benutzen'])
             ->setFrontendName(\Shop::Lang()->get('allCategories'));
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        if ($this->getIncludeSubCategories() === true) {
            return ' tkategorieartikel.kKategorie IN (
                        SELECT tchild.kKategorie FROM tkategorie AS tparent
                            JOIN tkategorie AS tchild
                                ON tchild.lft BETWEEN tparent.lft AND tparent.rght
                                WHERE tparent.kKategorie = ' . $this->getValue() . ')';
        }

        return $this->getConfig('navigationsfilter')['kategoriefilter_anzeigen_als'] === 'HF'
            ? '(tkategorieartikelgesamt.kOberKategorie = ' . $this->getValue() .
            ' OR tkategorieartikelgesamt.kKategorie = ' . $this->getValue() . ') '
            : ' tkategorieartikel.kKategorie = ' . $this->getValue();
    }

    /**
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        $join = (new FilterJoin())
            ->setOrigin(__CLASS__)
            ->setComment('join from ' . __METHOD__)
            ->setType('JOIN');
        if ($this->getConfig('navigationsfilter')['kategoriefilter_anzeigen_als'] === 'HF') {
            $join->setTable('(
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
    public function getOptions($data = null): array
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
            $this->getType()->equals(Type::OR())
                ? $this->getClassName()
                : null
        );
        $options            = [];
        $sql                = (new FilterStateSQL())->from($state);
        // Kategoriefilter anzeige
        if ($categoryFilterType === 'HF' && !$this->productFilter->hasCategory()) {
            //@todo: $this instead of $naviFilter->KategorieFilter?
            $kKatFilter = $this->productFilter->hasCategoryFilter()
                ? ''
                : ' AND tkategorieartikelgesamt.kOberKategorie = 0';

            $sql->addJoin((new FilterJoin())
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
                ->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel ' . $kKatFilter)
                ->setOrigin(__CLASS__));
            $sql->addJoin((new FilterJoin())
                ->setComment('join2 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tkategorie')
                ->setOn('tkategorie.kKategorie = tkategorieartikelgesamt.kKategorie')
                ->setOrigin(__CLASS__));
        } else {
            // @todo: this instead of $naviFilter->Kategorie?
            if (!$this->productFilter->hasCategory()) {
                $sql->addJoin((new FilterJoin())
                    ->setComment('join3 from ' . __METHOD__)
                    ->setType('JOIN')
                    ->setTable('tkategorieartikel')
                    ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel')
                    ->setOrigin(__CLASS__));
            }
            $sql->addJoin((new FilterJoin())
                ->setComment('join4 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tkategorie')
                ->setOn('tkategorie.kKategorie = tkategorieartikel.kKategorie')
                ->setOrigin(__CLASS__));
        }
        if (!\Shop::has('checkCategoryVisibility')) {
            \Shop::set(
                'checkCategoryVisibility',
                \Shop::Container()->getDB()->query(
                    'SELECT kKategorie FROM tkategoriesichtbarkeit',
                    ReturnType::AFFECTED_ROWS
                ) > 0
            );
        }
        if (\Shop::get('checkCategoryVisibility')) {
            $sql->addJoin((new FilterJoin())
                ->setComment('join5 from ' . __METHOD__)
                ->setType('LEFT JOIN')
                ->setTable('tkategoriesichtbarkeit')
                ->setOn('tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie')
                ->setOrigin(__CLASS__));

            $sql->addCondition('tkategoriesichtbarkeit.kKategorie IS NULL');
        }
        $cSQLKategorieSprache        = new \stdClass();
        $cSQLKategorieSprache->cJOIN = '';
        $select                      = ['tkategorie.kKategorie', 'tkategorie.nSort'];
        if (!\Sprache::isDefaultLanguageActive()) {
            $select[] = "IF(tkategoriesprache.cName = '', tkategorie.cName, tkategoriesprache.cName) AS cName";
            $sql->addJoin((new FilterJoin())
                ->setComment('join5 from ' . __METHOD__)
                ->setType('JOIN')
                ->setTable('tkategoriesprache')
                ->setOn('tkategoriesprache.kKategorie = tkategorie.kKategorie 
                            AND tkategoriesprache.kSprache = ' . $this->getLanguageID())
                ->setOrigin(__CLASS__));
        } else {
            $select[] = 'tkategorie.cName';
        }
        $sql->setSelect($select);
        $sql->setOrderBy(null);
        $sql->setLimit('');
        $sql->setGroupBy(['tkategorie.kKategorie', 'tartikel.kArtikel']);

        $query            = $this->productFilter->getFilterSQL()->getBaseQuery($sql);
        $categories       = \Shop::Container()->getDB()->executeQuery(
            "SELECT tseo.cSeo, ssMerkmal.kKategorie, ssMerkmal.cName, 
                ssMerkmal.nSort, COUNT(*) AS nAnzahl
                FROM (" . $query . " ) AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kKategorie
                        AND tseo.cKey = 'kKategorie'
                        AND tseo.kSprache = " . $this->getLanguageID() . "
                    GROUP BY ssMerkmal.kKategorie
                    ORDER BY ssMerkmal.nSort, ssMerkmal.cName",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $langID           = $this->getLanguageID();
        $customerGroupID  = $this->getCustomerGroupID();
        $additionalFilter = new self($this->productFilter);
        $helper           = \KategorieHelper::getInstance($langID, $customerGroupID);
        foreach ($categories as $category) {
            $category->kKategorie = (int)$category->kKategorie;
            if ($categoryFilterType === 'KP') { // category path
                $category->cName = $helper->getPath(new \Kategorie($category->kKategorie, $langID, $customerGroupID));
            }
            $options[] = (new FilterOption())
                ->setParam($this->getUrlParam())
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$category->kKategorie)
                ))
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setName($category->cName)
                ->setValue($category->kKategorie)
                ->setCount((int)$category->nAnzahl)
                ->setSort((int)$category->nSort);
        }
        if ($categoryFilterType === 'KP') {
            usort($options, function ($a, $b) {
                /** @var FilterOption $a */
                /** @var FilterOption $b */
                return strcmp($a->getName(), $b->getName());
            });
        }
        $this->options = $options;

        return $options;
    }
}
