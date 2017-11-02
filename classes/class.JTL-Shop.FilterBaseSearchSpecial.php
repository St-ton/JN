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
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    private static $mapping = [
        'kKey' => 'ValueCompat'
    ];

    /**
     * FilterBaseSearchSpecial constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct($productFilter)
    {
        parent::__construct($productFilter);
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
        $this->value = (int)$id;

        return $this;
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
            foreach ($oSeo_arr as $oSeo) {
                $oSeo->kSprache = (int)$oSeo->kSprache;
                if ($language->kSprache === $oSeo->kSprache) {
                    $this->cSeo[$language->kSprache] = $oSeo->cSeo;
                }
            }
        }
        switch ($this->getValue()) {
            case SEARCHSPECIALS_BESTSELLER:
                $this->setName(Shop::Lang()->get('bestsellers'));
                break;
            case SEARCHSPECIALS_SPECIALOFFERS:
                $this->setName(Shop::Lang()->get('specialOffers'));
                break;
            case SEARCHSPECIALS_NEWPRODUCTS:
                $this->setName(Shop::Lang()->get('newProducts'));
                break;
            case SEARCHSPECIALS_TOPOFFERS:
                $this->setName(Shop::Lang()->get('topOffers'));
                break;
            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                $this->setName(Shop::Lang()->get('upcomingProducts'));
                break;
            case SEARCHSPECIALS_TOPREVIEWS:
                $this->setName(Shop::Lang()->get('topReviews'));
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
     */
    public function getSQLCondition()
    {
        switch ($this->value) {
            case SEARCHSPECIALS_BESTSELLER:
                $nAnzahl = (($min = $this->getConfig()['global']['global_bestseller_minanzahl']) > 0)
                    ? (int)$min
                    : 100;
                return "ROUND(tbestseller.fAnzahl) >= " . $nAnzahl;

            case SEARCHSPECIALS_SPECIALOFFERS:
                $tasp = 'tartikelsonderpreis';
                $tsp  = 'tsonderpreise';
                if (!$this->productFilter->hasPriceRangeFilter()) {
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
                return 'now() < tartikel.dErscheinungsdatum';

            case SEARCHSPECIALS_TOPREVIEWS:
                if (!$this->productFilter->hasRatingFilter()) {
                    $nMindestSterne = ($min = $this->getConfig()['boxen']['boxen_topbewertet_minsterne']) > 0
                        ? (int)$min
                        : 4;

                    return ' ROUND(taex.fDurchschnittsBewertung) >= ' . $nMindestSterne;
                }
                break;

            default:
                break;
        }

        return '';
    }

    /**
     * @return array|FilterJoin
     */
    public function getSQLJoin()
    {
        switch ($this->value) {
            case SEARCHSPECIALS_BESTSELLER:
                return (new FilterJoin())
                    ->setType('JOIN')
                    ->setTable('tbestseller')
                    ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                    ->setComment('JOIN from FilterBaseSearchSpecial bestseller')
                    ->setOrigin(__CLASS__);

            case SEARCHSPECIALS_SPECIALOFFERS:
                return $this->productFilter->hasPriceRangeFilter()
                    ? []
                    : (new FilterJoin())
                        ->setType('JOIN')
                        ->setTable('tartikelsonderpreis AS tasp')
                        ->setOn('tasp.kArtikel = tartikel.kArtikel JOIN tsonderpreise AS tsp 
                                    ON tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis')
                        ->setComment('JOIN from FilterBaseSearchSpecial special offers')
                        ->setOrigin(__CLASS__);

            case SEARCHSPECIALS_NEWPRODUCTS:
            case SEARCHSPECIALS_TOPOFFERS:
            case SEARCHSPECIALS_UPCOMINGPRODUCTS:
                return [];

            case SEARCHSPECIALS_TOPREVIEWS:
                return $this->productFilter->hasRatingFilter()
                    ? []
                    : (new FilterJoin())
                        ->setType('JOIN')
                        ->setTable('tartikelext AS taex ')
                        ->setOn('taex.kArtikel = tartikel.kArtikel')
                        ->setComment('JOIN from FilterBaseSearchSpecial top reviews')
                        ->setOrigin(__CLASS__);

            default:
                return [];
        }
    }
}
