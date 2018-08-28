<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Filter\States;


use Filter\AbstractFilter;
use Filter\Join;
use Filter\FilterInterface;
use Filter\ProductFilter;

/**
 * Class BaseSearchSpecial
 * @package Filter\States
 */
class BaseSearchSpecial extends AbstractFilter
{
    use \MagicCompatibilityTrait;

    /**
     * @var array
     */
    public static $mapping = [
        'kKey' => 'ValueCompat'
    ];

    /**
     * BaseSearchSpecial constructor.
     *
     * @param ProductFilter $productFilter
     */
    public function __construct(ProductFilter $productFilter)
    {
        parent::__construct($productFilter);
        $this->setIsCustom(false)
             ->setUrlParam('q')
             ->setUrlParamSEO(null);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value): FilterInterface
    {
        $this->value = (int)$value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSeo(array $languages): FilterInterface
    {
        $oSeo_arr = $this->productFilter->getDB()->selectAll(
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
            case \SEARCHSPECIALS_BESTSELLER:
                $this->setName(\Shop::Lang()->get('bestsellers'));
                break;
            case \SEARCHSPECIALS_SPECIALOFFERS:
                $this->setName(\Shop::Lang()->get('specialOffers'));
                break;
            case \SEARCHSPECIALS_NEWPRODUCTS:
                $this->setName(\Shop::Lang()->get('newProducts'));
                break;
            case \SEARCHSPECIALS_TOPOFFERS:
                $this->setName(\Shop::Lang()->get('topOffers'));
                break;
            case \SEARCHSPECIALS_UPCOMINGPRODUCTS:
                $this->setName(\Shop::Lang()->get('upcomingProducts'));
                break;
            case \SEARCHSPECIALS_TOPREVIEWS:
                $this->setName(\Shop::Lang()->get('topReviews'));
                break;
            default:
                // invalid search special ID
                \Shop::$is404        = true;
                \Shop::$kSuchspecial = 0;
                break;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryKeyRow(): string
    {
        return 'kKey';
    }

    /**
     * @inheritdoc
     */
    public function getSQLCondition(): string
    {
        switch ($this->value) {
            case \SEARCHSPECIALS_BESTSELLER:
                $nAnzahl = (($min = $this->getConfig('global')['global_bestseller_minanzahl']) > 0)
                    ? (int)$min
                    : 100;

                return "ROUND(tbestseller.fAnzahl) >= " . $nAnzahl;

            case \SEARCHSPECIALS_SPECIALOFFERS:
                $tasp = 'tartikelsonderpreis';
                $tsp  = 'tsonderpreise';
                if (!$this->productFilter->hasPriceRangeFilter()) {
                    $tasp = 'tasp';
                    $tsp  = 'tsp';
                }

                return $tasp . " .kArtikel = tartikel.kArtikel
                                    AND " . $tasp . ".cAktiv = 'Y' AND " . $tasp . ".dStart <= NOW()
                                    AND (" . $tasp . ".dEnde >= CURDATE() OR " . $tasp . ".dEnde IS NULL)
                                    AND " . $tsp . " .kKundengruppe = " . \Session::CustomerGroup()->getID();

            case \SEARCHSPECIALS_NEWPRODUCTS:
                $alter_tage = (($age = $this->getConfig('boxen')['box_neuimsortiment_alter_tage']) > 0)
                    ? (int)$age
                    : 30;

                return "tartikel.cNeu = 'Y' 
                    AND DATE_SUB(now(), INTERVAL $alter_tage DAY) < tartikel.dErstellt 
                    AND tartikel.cNeu = 'Y'";

            case \SEARCHSPECIALS_TOPOFFERS:
                return "tartikel.cTopArtikel = 'Y'";

            case \SEARCHSPECIALS_UPCOMINGPRODUCTS:
                return 'now() < tartikel.dErscheinungsdatum';

            case \SEARCHSPECIALS_TOPREVIEWS:
                if (!$this->productFilter->hasRatingFilter()) {
                    $nMindestSterne = ($min = $this->getConfig('boxen')['boxen_topbewertet_minsterne']) > 0
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
     * @inheritdoc
     */
    public function getSQLJoin()
    {
        switch ($this->value) {
            case \SEARCHSPECIALS_BESTSELLER:
                return (new Join())
                    ->setType('JOIN')
                    ->setTable('tbestseller')
                    ->setOn('tbestseller.kArtikel = tartikel.kArtikel')
                    ->setComment('bestseller JOIN from ' . __METHOD__)
                    ->setOrigin(__CLASS__);

            case \SEARCHSPECIALS_SPECIALOFFERS:
                return $this->productFilter->hasPriceRangeFilter()
                    ? []
                    : (new Join())
                        ->setType('JOIN')
                        ->setTable('tartikelsonderpreis AS tasp')
                        ->setOn('tasp.kArtikel = tartikel.kArtikel JOIN tsonderpreise AS tsp 
                                    ON tsp.kArtikelSonderpreis = tasp.kArtikelSonderpreis')
                        ->setComment('special offers JOIN from ' . __METHOD__)
                        ->setOrigin(__CLASS__);

            case \SEARCHSPECIALS_NEWPRODUCTS:
            case \SEARCHSPECIALS_TOPOFFERS:
            case \SEARCHSPECIALS_UPCOMINGPRODUCTS:
                return [];

            case \SEARCHSPECIALS_TOPREVIEWS:
                return $this->productFilter->hasRatingFilter()
                    ? []
                    : (new Join())
                        ->setType('JOIN')
                        ->setTable('tartikelext AS taex ')
                        ->setOn('taex.kArtikel = tartikel.kArtikel')
                        ->setComment('top reviews JOIN from ' . __METHOD__)
                        ->setOrigin(__CLASS__);

            default:
                return [];
        }
    }
}
