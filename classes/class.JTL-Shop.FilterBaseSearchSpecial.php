<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterBaseSearchSpecial
 */
class FilterBaseSearchSpecial extends AbstractFilter
{
    /**
     * @var int
     */
    public $kKey = 0;

    /**
     * FilterBaseSearchSpecial constructor.
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
        $this->urlParam    = 'q';
        $this->urlParamSEO = null;
    }

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
        $oSeo_arr = Shop::DB()->selectAll(
            'tseo',
            ['cKey', 'kKey'],
            ['suchspecial', $this->getValue()],
            'cSeo, kSprache',
            'kSprache'
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
        switch ($this->kKey) {
            case SEARCHSPECIALS_BESTSELLER:
                $nAnzahl = (($min = $this->getConfig()['global']['global_bestseller_minanzahl']) > 0)
                    ? (int)$min
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
                $alter_tage = (($age = $this->getConfig()['boxen']['box_neuimsortiment_alter_tage']) > 0)
                    ? (int)$age
                    : 30;

                return "tartikel.cNeu = 'Y' AND DATE_SUB(now(),INTERVAL $alter_tage DAY) < tartikel.dErstellt AND tartikel.cNeu = 'Y'";

            case SEARCHSPECIALS_TOPOFFERS:
                return "tartikel.cTopArtikel = 'Y'";

            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                return "now() < tartikel.dErscheinungsdatum";

            case SEARCHSPECIALS_TOPREVIEWS:
                if (!Shop::getNaviFilter()->BewertungFilter->isInitialized()) {
                    $nMindestSterne = (($min = $this->getConfig()['boxen']['boxen_topbewertet_minsterne']) > 0)
                        ? (int)$min
                        : 4;

                    return " ROUND(taex.fDurchschnittsBewertung) >= " . $nMindestSterne;
                }
                break;

            default:
                break;
        }

        return '';
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
                     ->setComment('JOIN from FilterBaseSearchSpecial bestseller');

                return $join;

            case SEARCHSPECIALS_SPECIALOFFERS:
                if (!Shop::getNaviFilter()->PreisspannenFilter->isInitialized()) {
                    $join = new FilterJoin();
                    $join->setType('JOIN')
                         ->setTable('tartikelsonderpreis AS tasp')
                         ->setOn('tasp.kArtikel = tartikel.kArtikel JOIN tsonderpreise AS tsp 
                                      ON tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis')
                         ->setComment('JOIN from FilterBaseSearchSpecial special offers');

                    return $join;
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
                         ->setComment('JOIN from FilterBaseSearchSpecial top reviews');

                    return $join;
                }

                return [];

            default:
                return [];
        }
    }
}
