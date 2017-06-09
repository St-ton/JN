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
        $join = new FilterJoin();
        $join->setComment('join from FilterItemCategory')
             ->setType('JOIN');
        if ($this->getConfig()['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF') {
            $join->setTable('tkategorieartikelgesamt')
                 ->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel');
        }
        $join->setTable('tkategorieartikel')
             ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel');

        return $join;
    }

    /**
     * @param null $mixed
     * @return array|int|stdClass
     */
    public function getOptions($mixed = null)
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
            if ($categoryFilterType === 'HF' && (!$this->naviFilter->Kategorie->isInitialized())) {
                //@todo: $this instead of $naviFilter->KategorieFilter?
                $kKatFilter = $this->naviFilter->KategorieFilter->isInitialized()
                    ? ''
                    : " AND tkategorieartikelgesamt.kOberKategorie = 0";

                $state->joins[] = (new FilterJoin())
                    ->setComment('join1 from FilterItemCategory::getOptions()')
                    ->setType('JOIN')
                    ->setTable('tkategorieartikelgesamt')
                    ->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel ' . $kKatFilter);
                $state->joins[] = (new FilterJoin())
                    ->setComment('join2 from FilterItemCategory::getOptions()')
                    ->setType('JOIN')
                    ->setTable('tkategorie')
                    ->setOn('tkategorie.kKategorie = tkategorieartikelgesamt.kKategorie');
            } else {
                //@todo: this instead of $naviFilter->Kategorie?
                if (!$this->naviFilter->Kategorie->isInitialized()) {
                    $state->joins[] = (new FilterJoin())
                        ->setComment('join3 from FilterItemCategory::getOptions()')
                        ->setType('JOIN')
                        ->setTable('tkategorieartikel')
                        ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel');
                }
                $state->joins[] = (new FilterJoin())
                    ->setComment('join4 from FilterItemCategory::getOptions()')
                    ->setType('JOIN')
                    ->setTable('tkategorie')
                    ->setOn('tkategorie.kKategorie = tkategorieartikel.kKategorie');
            }
            $state->joins[] = (new FilterJoin())
                ->setComment('join5 from FilterItemCategory::getOptions()')
                ->setType('LEFT JOIN')
                ->setTable('tkategoriesichtbarkeit')
                ->setOn('tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie');

            $state->conditions[] = "tkategoriesichtbarkeit.kKategorie IS NULL";

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
                                AND tkategoriesprache.kSprache = ' . $this->getLanguageID());
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
            $query            = "SELECT tseo.cSeo, ssMerkmal.kKategorie, ssMerkmal.cName, 
                ssMerkmal.nSort, COUNT(*) AS nAnzahl
                FROM (" . $query . " ) AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kKategorie
                        AND tseo.cKey = 'kKategorie'
                        AND tseo.kSprache = " . $this->getLanguageID() . "
                    GROUP BY ssMerkmal.kKategorie
                    ORDER BY ssMerkmal.nSort, ssMerkmal.cName";
            $categories       = Shop::DB()->query($query, 2);
            $additionalFilter = new FilterItemCategory($this->naviFilter);
            foreach ($categories as $category) {
                // Anzeigen als KategoriePfad
                if ($categoryFilterType === 'KP') {
                    $oKategorie      = new Kategorie($category->kKategorie);
                    $category->cName = gibKategoriepfad(
                        $oKategorie,
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
            //neue Sortierung
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
