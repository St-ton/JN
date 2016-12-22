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
     * @var bool
     */
    public $isCustom = false;

    /**
     * @var string
     */
    public $urlParam = 'kf';

    /**
     * @var string
     */
    public $urlParamSEO = SEP_KAT;

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        $conf = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
        if ($conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF') {
            return '(tkategorieartikelgesamt.kOberKategorie = ' . $this->getValue() . ' OR tkategorieartikelgesamt.kKategorie = ' . $this->getValue() . ') ';
        }

        return ' tkategorieartikel.kKategorie = ' . $this->getValue();
    }

    /**
     * @return FilterJoin
     */
    public function getSQLJoin()
    {
        $conf = Shop::getSettings([CONF_NAVIGATIONSFILTER]);
        $join = new FilterJoin();
        $join->setComment('join from FilterItemCategory')
             ->setType('JOIN');
        if ($conf['navigationsfilter']['kategoriefilter_anzeigen_als'] === 'HF') {
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
        $oKategorieFilterDB_arr = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_kategoriefilter_benutzen'] !== 'N') {
            $naviFilter         = Shop::getNaviFilter();
            $categoryFilterType = $this->getConfig()['navigationsfilter']['kategoriefilter_anzeigen_als'];
            $order              = $naviFilter->getOrder();
            $state              = $naviFilter->getCurrentStateData();

            $state->joins[] = $order->join;

            // Kategoriefilter anzeige
            if ($categoryFilterType === 'HF' && (!$naviFilter->Kategorie->isInitialized())) {
                //@todo: $this instead of $naviFilter->KategorieFilter?
                $kKatFilter = ($naviFilter->KategorieFilter->isInitialized())
                    ? ''
                    : " AND tkategorieartikelgesamt.kOberKategorie = 0";

                $join = new FilterJoin();
                $join->setComment('join1 from FilterItemCategory::getOptions()')
                     ->setType('JOIN')
                     ->setTable('tkategorieartikelgesamt')
                     ->setOn('tartikel.kArtikel = tkategorieartikelgesamt.kArtikel ' . $kKatFilter);
                $state->joins[] = $join;

                $join = new FilterJoin();
                $join->setComment('join2 from FilterItemCategory::getOptions()')
                     ->setType('JOIN')
                     ->setTable('tkategorie')
                     ->setOn('tkategorie.kKategorie = tkategorieartikelgesamt.kKategorie');
                $state->joins[] = $join;
            } else {
                //@todo: this instead of $naviFilter->Kategorie?
                if (!$naviFilter->Kategorie->isInitialized()) {
                    $join = new FilterJoin();
                    $join->setComment('join3 from FilterItemCategory::getOptions()')
                         ->setType('JOIN')
                         ->setTable('tkategorieartikel')
                         ->setOn('tartikel.kArtikel = tkategorieartikel.kArtikel');
                    $state->joins[] = $join;
                }
                $join = new FilterJoin();
                $join->setComment('join4 from FilterItemCategory::getOptions()')
                     ->setType('JOIN')
                     ->setTable('tkategorie')
                     ->setOn('tkategorie.kKategorie = tkategorieartikel.kKategorie');
                $state->joins[] = $join;
            }
            $join = new FilterJoin();
            $join->setComment('join5 from FilterItemCategory::getOptions()')
                 ->setType('LEFT JOIN')
                 ->setTable('tkategoriesichtbarkeit')
                 ->setOn('tkategoriesichtbarkeit.kKategorie = tkategorie.kKategorie');
            $state->joins[] = $join;

            $state->conditions[] = "tkategoriesichtbarkeit.kKategorie IS NULL";

            // nicht Standardsprache? Dann hole Namen nicht aus tkategorie sondern aus tkategoriesprache
            $cSQLKategorieSprache        = new stdClass();
            $cSQLKategorieSprache->cJOIN = '';
            $select                      = ['tkategorie.kKategorie', 'tkategorie.nSort'];
            if (!standardspracheAktiv()) {
                $select[] = "IF(tkategoriesprache.cName = '', tkategorie.cName, tkategoriesprache.cName) AS cName";
                $join     = new FilterJoin();
                $join->setComment('join5 from FilterItemCategory::getOptions()')
                     ->setType('JOIN')
                     ->setTable('tkategoriesprache')
                     ->setOn('tkategoriesprache.kKategorie = tkategorie.kKategorie AND tkategoriesprache.kSprache = ' . $this->getLanguageID());
                $state->joins[] = $join;
            } else {
                $select[] = "tkategorie.cName";
            }

            $query                  = $naviFilter->getBaseQuery($select, $state->joins, $state->conditions, $state->having,
                $order->orderBy, '', ['tkategorie.kKategorie', 'tartikel.kArtikel']);
            $query                  = "SELECT tseo.cSeo, ssMerkmal.kKategorie, ssMerkmal.cName, ssMerkmal.nSort, COUNT(*) AS nAnzahl
                FROM (" . $query . " ) AS ssMerkmal
                    LEFT JOIN tseo ON tseo.kKey = ssMerkmal.kKategorie
                        AND tseo.cKey = 'kKategorie'
                        AND tseo.kSprache = " . $this->getLanguageID() . "
                    GROUP BY ssMerkmal.kKategorie
                    ORDER BY ssMerkmal.nSort, ssMerkmal.cName";
            $oKategorieFilterDB_arr = Shop::DB()->query($query, 2);
            //baue URL
            $count                          = (is_array($oKategorieFilterDB_arr)) ? count($oKategorieFilterDB_arr) : 0;
            $oZusatzFilter                  = new stdClass();
            $oZusatzFilter->KategorieFilter = new stdClass();
            for ($i = 0; $i < $count; ++$i) {
                // Anzeigen als KategoriePfad
                if ($categoryFilterType === 'KP') {
                    $oKategorie                        = new Kategorie($oKategorieFilterDB_arr[$i]->kKategorie);
                    $oKategorieFilterDB_arr[$i]->cName = gibKategoriepfad($oKategorie, $this->getCustomerGroupID(), $this->getLanguageID());
                }
                $oZusatzFilter->KategorieFilter->kKategorie = (int)$oKategorieFilterDB_arr[$i]->kKategorie;
                $oZusatzFilter->KategorieFilter->cSeo       = $oKategorieFilterDB_arr[$i]->cSeo;
                $oKategorieFilterDB_arr[$i]->cURL           = $naviFilter->getURL(true, $oZusatzFilter);
                $oKategorieFilterDB_arr[$i]->nAnzahl        = (int)$oKategorieFilterDB_arr[$i]->nAnzahl;
                $oKategorieFilterDB_arr[$i]->kKategorie     = (int)$oKategorieFilterDB_arr[$i]->kKategorie;
                $oKategorieFilterDB_arr[$i]->nSort          = (int)$oKategorieFilterDB_arr[$i]->nSort;
            }
            //neue Sortierung
            if ($categoryFilterType === 'KP') {
                usort($oKategorieFilterDB_arr, 'sortierKategoriepfade');
            }
        }

        return $oKategorieFilterDB_arr;
    }
}
