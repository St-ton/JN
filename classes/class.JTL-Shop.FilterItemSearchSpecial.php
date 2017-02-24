<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemSearchSpecial
 */
class FilterItemSearchSpecial extends AbstractFilter implements IFilter
{
    use FilterItemTrait;

    /**
     * @var string
     */
    public $urlParam = 'qf';

    /**
     * @var bool
     */
    public $isCustom = false;

    /**
     * @var int
     */
    public $kKey = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setValue($id)
    {
        $this->kKey = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->kKey;
    }

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages)
    {
        $oSeo_arr = Shop::DB()->query("
                SELECT cSeo, kSprache
                    FROM tseo
                    WHERE cKey = 'suchspecial'
                        AND kKey = " . $this->getValue() . "
                    ORDER BY kSprache", 2
        );

        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (is_array($oSeo_arr)) {
                foreach ($oSeo_arr as $oSeo) {
                    $oSeo->kSprache = (int)$oSeo->kSprache;
                    if ($language->kSprache === $oSeo->kSprache) {
                        $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                    }
                }
            }
        }
        switch ($this->getValue()) {
            case SEARCHSPECIALS_BESTSELLER:
                $this->cName = Shop::Lang()->get('bestsellers', 'global');
                break;
            case SEARCHSPECIALS_SPECIALOFFERS:
                $this->cName = Shop::Lang()->get('specialOffers', 'global');
                break;
            case SEARCHSPECIALS_NEWPRODUCTS:
                $this->cName = Shop::Lang()->get('newProducts', 'global');
                break;
            case SEARCHSPECIALS_TOPOFFERS:
                $this->cName = Shop::Lang()->get('topOffers', 'global');
                break;
            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                $this->cName = Shop::Lang()->get('upcomingProducts', 'global');
                break;
            case SEARCHSPECIALS_TOPREVIEWS:
                $this->cName = Shop::Lang()->get('topReviews', 'global');
                break;
            default:
                //invalid search special ID
                Shop::$is404        = true;
                Shop::$kSuchspecial = 0;
                break;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKeyRow()
    {
        return 'kKey';
    }

    /**
     * @return string
     * @todo
     */
    public function getTableName()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getSQLCondition()
    {
        $conf = Shop::getSettings([CONF_BOXEN, CONF_GLOBAL]);
        switch ($this->kKey) {
            case SEARCHSPECIALS_BESTSELLER:
                $nAnzahl = (isset($conf['global']['global_bestseller_minanzahl'])
                    && (int)$conf['global']['global_bestseller_minanzahl'] > 0)
                    ? (int)$conf['global']['global_bestseller_minanzahl']
                    : 100;

                return "ROUND(tbestseller.fAnzahl) >= " . $nAnzahl;

            case SEARCHSPECIALS_SPECIALOFFERS:
                $tasp = 'tartikelsonderpreis';
                $tsp  = 'tsonderpreise';
                if (!Shop::getNaviFilter()->PreisspannenFilter->isInitialized()) {
                    $tasp = 'tasp';
                    $tsp  = 'tsp';
                }
                return $tasp . " .kArtikel = tartikel.kArtikel
                                    AND " . $tasp . ".cAktiv = 'Y' AND " . $tasp . ".dStart <= now()
                                    AND (" . $tasp . ".dEnde >= curdate() OR " . $tasp . ".dEnde = '0000-00-00')
                                    AND " . $tsp . " .kKundengruppe = " . (int)$_SESSION['Kundengruppe']->kKundengruppe;
//@todo:
//                $oFilter->tasp = $tasp;
//                $oFilter->tsp  = $tsp;

                break;

            case SEARCHSPECIALS_NEWPRODUCTS:
                $alter_tage = ($conf['boxen']['box_neuimsortiment_alter_tage'] > 0)
                    ? (int)$conf['boxen']['box_neuimsortiment_alter_tage']
                    : 30;

                return "tartikel.cNeu = 'Y' AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < tartikel.dErstellt AND tartikel.cNeu = 'Y'";

            case SEARCHSPECIALS_TOPOFFERS:
                return "tartikel.cTopArtikel = 'Y'";

            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                return "now() < tartikel.dErscheinungsdatum";

            case SEARCHSPECIALS_TOPREVIEWS:
                if (!Shop::getNaviFilter()->BewertungFilter->isInitialized()) {
                    $nMindestSterne = ((int)$conf['boxen']['boxen_topbewertet_minsterne'] > 0)
                        ? (int)$conf['boxen']['boxen_topbewertet_minsterne']
                        : 4;

                    return "ROUND(taex.fDurchschnittsBewertung) >= " . $nMindestSterne;
                }
                break;

            default:
                break;
        }
    }

    /**
     * @return string
     */
    public function getSQLJoin()
    {
        switch ($this->kKey) {
            case SEARCHSPECIALS_BESTSELLER:
                $join = new FilterJoin();
                $join->setType('JOIN')
                     ->setTable('tbestseller')
                     ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                     ->setComment('JOIN from FilterItemSearchSpecial bestseller');
                return $join;

            case SEARCHSPECIALS_SPECIALOFFERS:
                if (!Shop::getNaviFilter()->PreisspannenFilter->isInitialized()) {
                    $join = new FilterJoin();
                    $join->setType('JOIN')
                         ->setTable('tartikelsonderpreis AS tasp')
                         ->setOn('tasp.kArtikel = tartikel.kArtikel')
                         ->setComment('JOIN from FilterItemSearchSpecial special offers');

                    $join2 = new FilterJoin();
                    $join2->setType('JOIN')
                          ->setTable('tsonderpreise AS tsp')
                          ->setOn('tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis')
                          ->setComment('JOIN2 from FilterItemSearchSpecial special offers');

                    return [$join, $join2];
                }

                return [];

            case SEARCHSPECIALS_NEWPRODUCTS:
            case SEARCHSPECIALS_TOPOFFERS:
            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                return [];

            case SEARCHSPECIALS_TOPREVIEWS:
                if (!Shop::getNaviFilter()->BewertungFilter->isInitialized()) {
                    $join = new FilterJoin();
                    $join->setType('JOIN')
                         ->setTable('tartikelext AS taex ')
                         ->setOn('taex.kArtikel = tartikel.kArtikel')
                         ->setComment('JOIN from FilterItemSearchSpecial top reviews');

                    return $join;
                }

                return [];

            default:
                return [];
        }
    }

    /**
     * @param null $mixed
     * @return array
     */
    public function getOptions($mixed = null)
    {
        $oSuchspecialFilterDB_arr = [];
        if ($this->getConfig()['navigationsfilter']['allgemein_suchspecialfilter_benutzen'] === 'Y') {
            $naviFilter = Shop::getNaviFilter();
            for ($i = 1; $i < 7; ++$i) {
                $state = $naviFilter->getCurrentStateData();
                switch ($i) {
                    case SEARCHSPECIALS_BESTSELLER:
                        $nAnzahl = (($min = $this->getConfig()['global']['global_bestseller_minanzahl']) > 0)
                            ? (int)$min
                            : 100;

                        $join = new FilterJoin();
                        $join->setComment('join from FilterItemSearchSpecial::getOptions() bestseller')
                             ->setType('JOIN')
                             ->setTable('tbestseller')
                             ->setOn('tbestseller.kArtikel = tartikel.kArtikel');
                        $state->joins[] = $join;

                        $state->conditions[] = 'ROUND(tbestseller.fAnzahl) >= ' . $nAnzahl;
                        break;
                    case SEARCHSPECIALS_SPECIALOFFERS:
                        if (!$this->isInitialized()) {
                            $join = new FilterJoin();
                            $join->setComment('join1 from FilterItemSearchSpecial::getOptions() special offer')
                                 ->setType('JOIN')
                                 ->setTable('tartikelsonderpreis')
                                 ->setOn('tartikelsonderpreis.kArtikel = tartikel.kArtikel');
                            $state->joins[] = $join;

                            $join = new FilterJoin();
                            $join->setComment('join2 from FilterItemSearchSpecial::getOptions() special offer')
                                 ->setType('JOIN')
                                 ->setTable('tsonderpreise')
                                 ->setOn('tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis');
                            $state->joins[] = $join;
                            $tsonderpreise  = 'tsonderpreise';
                        } else {
                            $tsonderpreise = 'tsonderpreise';//'tspgspqf';
                        }
                        $state->conditions[] = "tartikelsonderpreis.cAktiv = 'Y' AND tartikelsonderpreis.dStart <= now()";
                        $state->conditions[] = "(tartikelsonderpreis.dEnde >= CuRDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')";
                        $state->conditions[] = $tsonderpreise . ".kKundengruppe = " . $this->getCustomerGroupID();
                        break;
                    case SEARCHSPECIALS_NEWPRODUCTS:
                        $alter_tage          = (($age = $this->getConfig()['boxen']['box_neuimsortiment_alter_tage']) > 0)
                            ? (int)$age
                            : 30;
                        $state->conditions[] = "tartikel.cNeu = 'Y' AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < tartikel.dErstellt";
                        break;
                    case SEARCHSPECIALS_TOPOFFERS:
                        $state->conditions[] = 'tartikel.cTopArtikel = "Y"';
                        break;
                    case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                        $state->conditions[] = 'now() < tartikel.dErscheinungsdatum';
                        break;
                    case SEARCHSPECIALS_TOPREVIEWS:
                        if (!$naviFilter->BewertungFilter->isInitialized()) {
                            $join = new FilterJoin();
                            $join->setComment('join from FilterItemSearchSpecial::getOptions() top reviews')
                                 ->setType('JOIN')
                                 ->setTable('tartikelext')
                                 ->setOn('tartikelext.kArtikel = tartikel.kArtikel');
                            $state->joins[] = $join;
                        }
                        $state->conditions[] = "ROUND(tartikelext.fDurchschnittsBewertung) >= " .
                            (int)$this->getConfig()['boxen']['boxen_topbewertet_minsterne'];
                        break;
                }
                $qry                   = $naviFilter->getBaseQuery(
                    ['tartikel.kArtikel'],
                    $state->joins,
                    $state->conditions,
                    $state->having
                );
                $oSuchspecialFilterDB  = Shop::DB()->query($qry, 2);
                $oSuchspecial          = new stdClass();
                $oSuchspecial->nAnzahl = count($oSuchspecialFilterDB);
                $oSuchspecial->kKey    = $i;

                $oZusatzFilter                          = new stdClass();
                $oZusatzFilter->SuchspecialFilter       = new stdClass();
                $oZusatzFilter->SuchspecialFilter->kKey = $i;
                $oSuchspecial->cURL                     = $naviFilter->getURL(true, $oZusatzFilter);
                $oSuchspecialFilterDB_arr[$i]           = $oSuchspecial;
            }
        }

        return $oSuchspecialFilterDB_arr;
    }
}
