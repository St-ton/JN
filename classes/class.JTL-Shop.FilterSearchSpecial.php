<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterSearchSpecial
 */
class FilterSearchSpecial extends AbstractFilter implements IFilter
{
    /**
     * @var int
     */
    public $kKey = 0;

    /**
     * @param int $id
     * @return $this
     */
    public function setID($id)
    {
        $this->kKey = (int)$id;

        return $this;
    }

    /**
     * @return int
     */
    public function getID()
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
                        AND kKey = " . $this->getID() . "
                    ORDER BY kSprache", 2
        );

        foreach ($languages as $language) {
            $this->cSeo[$language->kSprache] = '';
            if (is_array($oSeo_arr)) {
                foreach ($oSeo_arr as $oSeo) {
                    if ($language->kSprache == $oSeo->kSprache) {
                        $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                    }
                }
            }
        }
        switch ($this->getID()) {
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
//        $kKey = (isset($NaviFilter->Suchspecial->kKey)) ? $NaviFilter->Suchspecial->kKey : null;
//        if (isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0) {
//            $kKey = $NaviFilter->SuchspecialFilter->kKey;
//        }
        $conf = Shop::getSettings(array(CONF_BOXEN, CONF_GLOBAL));
        switch ($this->kKey) {
            case SEARCHSPECIALS_BESTSELLER:
                $nAnzahl = (isset($conf['global']['global_bestseller_minanzahl'])
                    && intval($conf['global']['global_bestseller_minanzahl'] > 0))
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
                $alter_tage      = ($conf['boxen']['box_neuimsortiment_alter_tage'] > 0)
                    ? (int)$conf['boxen']['box_neuimsortiment_alter_tage']
                    : 30;
                return "tartikel.cNeu = 'Y' AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < tartikel.dErstellt AND tartikel.cNeu = 'Y'";

            case SEARCHSPECIALS_TOPOFFERS:
                return "tartikel.cTopArtikel = 'Y'";

            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                return "now() < tartikel.dErscheinungsdatum";

            case SEARCHSPECIALS_TOPREVIEWS:
                if (!Shop::getNaviFilter()->BewertungFilter->isInitialized()) {
                    $nMindestSterne  = (intval($conf['boxen']['boxen_topbewertet_minsterne'] > 0))
                        ? (int)$conf['boxen']['boxen_topbewertet_minsterne']
                        : 4;
                    return " AND round(taex.fDurchschnittsBewertung) >= " . $nMindestSterne;
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
        //        $kKey = (isset($NaviFilter->Suchspecial->kKey)) ? $NaviFilter->Suchspecial->kKey : null;
//        if (isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0) {
//            $kKey = $NaviFilter->SuchspecialFilter->kKey;
//        }
        switch ($this->kKey) {
            case SEARCHSPECIALS_BESTSELLER:
                $join = new FilterJoin();
                $join->setType('JOIN')
                     ->setTable('tbestseller')
                     ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                     ->setComment('JOIN from FilterSearchSpecial bestseller');
                return [$join];
//                return "JOIN tbestseller ON tbestseller.kArtikel = tartikel.kArtikel";

            case SEARCHSPECIALS_SPECIALOFFERS:
                if (!Shop::getNaviFilter()->PreisspannenFilter->isInitialized()) {
                    $join = new FilterJoin();
                    $join->setType('JOIN')
                         ->setTable('tartikelsonderpreis AS tasp')
                         ->setOn('tasp.kArtikel = tartikel.kArtikel JOIN tsonderpreise AS tsp ON tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis')
                         ->setComment('JOIN from FilterSearchSpecial special offers');
                    return [$join];
//                    return "JOIN tartikelsonderpreis AS tasp ON tasp.kArtikel = tartikel.kArtikel\nJOIN tsonderpreise AS tsp ON tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis";
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
                         ->setComment('JOIN from FilterSearchSpecial top reviews');
                    return [$join];
//                    return "JOIN tartikelext AS taex ON taex.kArtikel = tartikel.kArtikel";
                }
                return [];

            default:
                return [];
        }
    }
}
