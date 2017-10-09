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
    use FilterItemTrait;

    /**
     * FilterItemCategory constructor.
     *
     * @param Navigationsfilter $naviFilter
     */
    public function __construct($naviFilter)
    {
        parent::__construct($naviFilter);
        $this->isCustom    = false;
        $this->urlParam    = 'kf';
        $this->urlParamSEO = SEP_KAT;
        $this->setVisibility($this->getConfig()['navigationsfilter']['allgemein_kategoriefilter_benutzen'])
             ->setFrontendName(Shop::Lang()->get('allCategories'));
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
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
     * @return array|int|stdClass
     */
    public function getOptions($data = null)
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $options = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_kategoriefilter_benutzen'] !== 'N') {
            $categoryFilterType = $this->getConfig()['navigationsfilter']['kategoriefilter_anzeigen_als'];
            $order              = $this->naviFilter->getOrder();
            $state              = $this->naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;
            // Kategoriefilter anzeige
            if ($categoryFilterType === 'HF' && (!$this->naviFilter->hasCategory())) {
                //@todo: $this instead of $naviFilter->KategorieFilter?
                $kKatFilter = $this->naviFilter->hasCategoryFilter()
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
                if (!$this->naviFilter->hasCategory()) {
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
            $state->joins[] = (new FilterJoin())
                ->setComment('join5 from FilterItemCategory::getOptions()')
                ->setType('LEFT JOIN')
                ->setTable('tkategoriesichtbarkeit')
                ->setOn('tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie')
                ->setOrigin(__CLASS__);

            $state->conditions[] = 'tkategoriesichtbarkeit.kKategorie IS NULL';
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

            $query            = $this->naviFilter->getBaseQuery(
                $select,
                $state->joins,
                $state->conditions,
                $state->having,
                $order->orderBy,
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
            $additionalFilter = new self($this->naviFilter);
            foreach ($categories as $category) {
                // Anzeigen als Kategoriepfad
                if ($categoryFilterType === 'KP') {
                    $category->cName = gibKategoriepfad(
                        new Kategorie($category->kKategorie, $this->getLanguageID(), $this->getCustomerGroupID()),
                        $this->getCustomerGroupID(),
                        $this->getLanguageID()
                    );
                }
                $fe             = (new FilterExtra())
                    ->setType($this->getType())
                    ->setClassName($this->getClassName())
                    ->setParam($this->getUrlParam())
                    ->setName($category->cName)
                    ->setValue((int)$category->kKategorie)
                    ->setCount($category->nAnzahl)
                    ->setSort($category->nSort)
                    ->setURL($this->naviFilter->getURL(
                        true,
                        $additionalFilter->init((int)$category->kKategorie)
                    ));
                $fe->kKategorie = (int)$category->kKategorie;
                $options[]      = $fe;
            }
            // neue Sortierung
            if ($categoryFilterType === 'KP') {
                usort($options, function ($a, $b) {
                    /** @var FilterExtra $a */
                    /** @var FilterExtra $b */
                    return strcmp($a->getName(), $b->getName());
                });
            }
        }
        $this->options = $options;

        return $options;
    }
}
