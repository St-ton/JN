<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemCategory
 */
class FilterItemCategory extends FilterBaseCategory
{
    /**
     * FilterItemCategory constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('kf')
             ->setUrlParamSEO(SEP_KAT)
             ->setVisibility($this->getConfig()['navigationsfilter']['allgemein_kategoriefilter_benutzen'])
             ->setFrontendName(Shop::Lang()->get('allCategories'));
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        if ($this->getIncludeSubCategories() === true) {
            return ' tkategorieartikel.kKategorie IN (
                        SELECT tchild.kKategorie FROM tkategorie AS tparent
                            JOIN tkategorie AS tchild
                                ON tchild.lft BETWEEN tparent.lft AND tparent.rght
                                WHERE tparent.kKategorie = ' . $this->getValue() . ')';
        }

        return $this->getConfig()['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF'
            ? '(tkategorieartikelgesamt.kOberKategorie = ' . $this->getValue() .
                ' OR tkategorieartikelgesamt.kKategorie = ' . $this->getValue() . ') '
            : ' tkategorieartikel.kKategorie = ' . $this->getValue();
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        $join = (new FilterJoin())
            ->setOrigin(__CLASS__)
            ->setComment('join from FilterItemCategory')
            ->setType('JOIN');
        if ($this->getConfig()['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF') {
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
     * @param null $data
     * @return FilterOption[]
     */
    public function getOptions($data = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        if ($this->getConfig()['navigationsfilter']['allgemein_kategoriefilter_benutzen'] === 'N') {
            $this->options = [];

            return $this->options;
        }
        $categoryFilterType = $this->getConfig()['navigationsfilter']['kategoriefilter_anzeigen_als'];
        $state              = $this->productFilter->getCurrentStateData();
        $options            = [];
        // Kategoriefilter anzeige
        if ($categoryFilterType === 'HF' && (!$this->productFilter->hasCategory())) {
            //@todo: $this instead of $naviFilter->KategorieFilter?
            $kKatFilter = $this->productFilter->hasCategoryFilter()
                ? ''
                : ' AND tkategorieartikelgesamt.kOberKategorie = 0';

            $state->joins[] = (new FilterJoin())
                ->setComment('join1 from FilterItemCategory::getOptions()')
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
                ->setOrigin(__CLASS__);
            $state->joins[] = (new FilterJoin())
                ->setComment('join2 from FilterItemCategory::getOptions()')
                ->setType('JOIN')
                ->setTable('tkategorie')
                ->setOn('tkategorie.kKategorie = tkategorieartikelgesamt.kKategorie')
                ->setOrigin(__CLASS__);
        } else {
            // @todo: this instead of $naviFilter->Kategorie?
            if (!$this->productFilter->hasCategory()) {
                $state->joins[] = (new FilterJoin())
                    ->setComment('join3 from FilterItemCategory::getOptions()')
                    ->setType('JOIN')
                    ->setTable('tkategorieartikel')
                    ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel')
                    ->setOrigin(__CLASS__);
            }
            $state->joins[] = (new FilterJoin())
                ->setComment('join4 from FilterItemCategory::getOptions()')
                ->setType('JOIN')
                ->setTable('tkategorie')
                ->setOn('tkategorie.kKategorie = tkategorieartikel.kKategorie')
                ->setOrigin(__CLASS__);
        }
        if (!Shop::has('checkCategoryVisibility')) {
            Shop::set(
                'checkCategoryVisibility',
                Shop::DB()->query('SELECT kKategorie FROM tkategoriesichtbarkeit', NiceDB::RET_AFFECTED_ROWS) > 0
            );
        }
        if (Shop::get('checkCategoryVisibility')) {
            $state->joins[] = (new FilterJoin())
                ->setComment('join5 from FilterItemCategory::getOptions()')
                ->setType('LEFT JOIN')
                ->setTable('tkategoriesichtbarkeit')
                ->setOn('tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie')
                ->setOrigin(__CLASS__);

            $state->conditions[] = 'tkategoriesichtbarkeit.kKategorie IS NULL';
        }
        // nicht Standardsprache? Dann hole Namen nicht aus tkategorie sondern aus tkategoriesprache
        $cSQLKategorieSprache        = new stdClass();
        $cSQLKategorieSprache->cJOIN = '';
        $select                      = ['tkategorie.kKategorie', 'tkategorie.nSort'];
        if (!standardspracheAktiv()) {
            $select[] = "IF(tkategoriesprache.cName = '', tkategorie.cName, tkategoriesprache.cName) AS cName";
            $state->joins[] = (new FilterJoin())
                ->setComment('join5 from FilterItemCategory::getOptions()')
                ->setType('JOIN')
                ->setTable('tkategoriesprache')
                ->setOn('tkategoriesprache.kKategorie = tkategorie.kKategorie 
                            AND tkategoriesprache.kSprache = ' . $this->getLanguageID())
                ->setOrigin(__CLASS__);
        } else {
            $select[] = 'tkategorie.cName';
        }

        $query            = $this->productFilter->getFilterSQL()->getBaseQuery(
            $select,
            $state->joins,
            $state->conditions,
            $state->having,
            null,
            '',
            ['tkategorie.kKategorie', 'tartikel.kArtikel']
        );
        $categories       = Shop::DB()->executeQuery(
            "SELECT tseo.cSeo, ssMerkmal.kKategorie, ssMerkmal.cName, 
                ssMerkmal.nSort, COUNT(*) AS nAnzahl
                FROM (" . $query . " ) AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kKategorie
                        AND tseo.cKey = 'kKategorie'
                        AND tseo.kSprache = " . $this->getLanguageID() . "
                    GROUP BY ssMerkmal.kKategorie
                    ORDER BY ssMerkmal.nSort, ssMerkmal.cName"
                , 2
        );
        $langID           = $this->getLanguageID();
        $customerGroupID  = $this->getCustomerGroupID();
        $additionalFilter = new self($this->productFilter);
        $helper           = KategorieHelper::getInstance($langID, $customerGroupID);
        foreach ($categories as $category) {
            // Anzeigen als Kategoriepfad
            if ($categoryFilterType === 'KP') {
                $category->cName = $helper->getPath(new Kategorie($category->kKategorie, $langID, $customerGroupID));
            }
            $options[] = (new FilterOption())
                ->setType($this->getType())
                ->setClassName($this->getClassName())
                ->setParam($this->getUrlParam())
                ->setName($category->cName)
                ->setValue((int)$category->kKategorie)
                ->setCount($category->nAnzahl)
                ->setSort($category->nSort)
                ->setURL($this->productFilter->getFilterURL()->getURL(
                    $additionalFilter->init((int)$category->kKategorie)
                ));
        }
        // neue Sortierung
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
