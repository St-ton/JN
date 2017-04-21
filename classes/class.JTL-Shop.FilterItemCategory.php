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
     * @param int|null   $languageID
     * @param int|null   $customerGroupID
     * @param array|null $config
     * @param array|null $languages
     */
    public function __construct($languageID = null, $customerGroupID = null, $config = null, $languages = null)
    {
        parent::__construct($languageID, $customerGroupID, $config, $languages);
        $this->isCustom    = false;
        $this->urlParam    = 'kf';
        $this->urlParamSEO = SEP_KAT;
        $this->setVisibility($config['navigationsfilter']['allgemein_kategoriefilter_benutzen']);
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        if ($this->getConfig()['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF') {
            return '(tkategorieartikelgesamt.kOberKategorie = ' . $this->getValue() .
                ' OR tkategorieartikelgesamt.kKategorie = ' . $this->getValue() . ') ';
        }

        return ' tkategorieartikel.kKategorie = ' . $this->getValue();
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
            $join->setTable('tkategorieartikelgesamt')->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel');
        }
        $join->setTable('tkategorieartikel')->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel');

        return $join;
    }

    /**
     * @param null $mixed
     * @return array|int|object
     */
    public function getOptions($mixed = null)
    {
        $categories = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_kategoriefilter_benutzen'] !== 'N') {
            $naviFilter         = Shop::getNaviFilter();
            $categoryFilterType = $this->getConfig()['navigationsfilter']['kategoriefilter_anzeigen_als'];
            $order              = $naviFilter->getOrder();
            $state              = $naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;

            // Kategoriefilter anzeige
            if ($categoryFilterType === 'HF' && (!$naviFilter->Kategorie->isInitialized())) {
                //@todo: $this instead of $naviFilter->KategorieFilter?
                $kKatFilter = $naviFilter->KategorieFilter->isInitialized()
                    ? ''
                    : " AND tkategorieartikelgesamt.kOberKategorie = 0";

                $state->joins[] = (new FilterJoin())->setComment('join1 from FilterItemCategory::getOptions()')
                                                    ->setType('JOIN')
                                                    ->setTable('tkategorieartikelgesamt')
                                                    ->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel ' .
                                                        $kKatFilter);
                $state->joins[] = (new FilterJoin())->setComment('join2 from FilterItemCategory::getOptions()')
                                                    ->setType('JOIN')
                                                    ->setTable('tkategorie')
                                                    ->setOn('tkategorie.kKategorie = tkategorieartikelgesamt.kKategorie');
            } else {
                //@todo: this instead of $naviFilter->Kategorie?
                if (!$naviFilter->Kategorie->isInitialized()) {
                    $state->joins[] = (new FilterJoin())->setComment('join3 from FilterItemCategory::getOptions()')
                                                        ->setType('JOIN')
                                                        ->setTable('tkategorieartikel')
                                                        ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel');
                }
                $state->joins[] = (new FilterJoin())->setComment('join4 from FilterItemCategory::getOptions()')
                                                    ->setType('JOIN')
                                                    ->setTable('tkategorie')
                                                    ->setOn('tkategorie.kKategorie = tkategorieartikel.kKategorie');
            }
            $state->joins[] = (new FilterJoin())->setComment('join5 from FilterItemCategory::getOptions()')
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
                $state->joins[] = (new FilterJoin())->setComment('join5 from FilterItemCategory::getOptions()')
                                                    ->setType('JOIN')
                                                    ->setTable('tkategoriesprache')
                                                    ->setOn('tkategoriesprache.kKategorie = tkategorie.kKategorie 
                                  AND tkategoriesprache.kSprache = ' . $this->getLanguageID());
            } else {
                $select[] = "tkategorie.cName";
            }

            $query            = $naviFilter->getBaseQuery(
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
            $additionalFilter = new FilterItemCategory(
                $this->getLanguageID(),
                $this->getCustomerGroupID(),
                $this->getConfig(),
                $this->getAvailableLanguages()
            );
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
                $category->cURL       = $naviFilter->getURL(
                    true,
                    $additionalFilter->init((int)$category->kKategorie)
                );
                $category->nAnzahl    = (int)$category->nAnzahl;
                $category->kKategorie = (int)$category->kKategorie;
                $category->nSort      = (int)$category->nSort;
            }
            //neue Sortierung
            if ($categoryFilterType === 'KP') {
                usort($oKategorieFilterDB_arr, function ($a, $b) { return strcmp($a->cName, $b->cName); });
            }
        }

        return $categories;
    }
}
