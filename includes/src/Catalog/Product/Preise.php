<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog\Product;

use JTL\Catalog\Currency;
use JTL\DB\ReturnType;
use JTL\Helpers\Tax;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class Preise
 * @package JTL\Catalog\Product
 */
class Preise
{
    /**
     * @var int
     */
    public $kKundengruppe;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var array
     */
    public $cVKLocalized;

    /**
     * @var float
     */
    public $fVKNetto = 0.0;

    /**
     * @var float
     */
    public $fVKBrutto = 0.0;

    /**
     * @var float
     */
    public $fPreis1;

    /**
     * @var float
     */
    public $fPreis2;

    /**
     * @var float
     */
    public $fPreis3;

    /**
     * @var float
     */
    public $fPreis4;

    /**
     * @var float
     */
    public $fPreis5;

    /**
     * @var float
     */
    public $fUst;

    /**
     * @var float
     */
    public $alterVKNetto;

    /**
     * @var int
     */
    public $nAnzahl1;

    /**
     * @var int
     */
    public $nAnzahl2;

    /**
     * @var int
     */
    public $nAnzahl3;

    /**
     * @var int
     */
    public $nAnzahl4;

    /**
     * @var int
     */
    public $nAnzahl5;

    /**
     * @var array
     */
    public $alterVK;

    /**
     * @var float
     */
    public $rabatt;

    /**
     * @var array
     */
    public $alterVKLocalized;

    /**
     * @var array
     */
    public $fVK;

    /**
     * @var array
     */
    public $nAnzahl_arr = [];

    /**
     * @var array
     */
    public $fPreis_arr = [];

    /**
     * @var array
     */
    public $fStaffelpreis_arr = [];

    /**
     * @var array
     */
    public $cPreisLocalized_arr = [];

    /**
     * @var bool|int
     */
    public $Sonderpreis_aktiv = false;

    /**
     * @var bool
     */
    public $Kundenpreis_aktiv = false;

    /**
     * @var PriceRange
     */
    public $oPriceRange;

    /**
     * @var string
     */
    public $SonderpreisBis_en;

    /**
     * @var string
     */
    public $SonderpreisBis_de;

    /**
     * Preise constructor.
     * @param int $kKundengruppe
     * @param int $kArtikel
     * @param int $kKunde
     * @param int $kSteuerklasse
     */
    public function __construct(int $kKundengruppe, int $kArtikel, int $kKunde = 0, int $kSteuerklasse = 0)
    {
        $db          = Shop::Container()->getDB();
        $filterKunde = 'AND p.kKundengruppe = ' . $kKundengruppe;
        if ($kKunde > 0 && $this->hasCustomPrice($kKunde)) {
            $filterKunde = 'AND (p.kKundengruppe, COALESCE(p.kKunde, 0)) = (
                            SELECT min(IFNULL(p1.kKundengruppe, ' . $kKundengruppe . ')), max(IFNULL(p1.kKunde, 0))
                            FROM tpreis AS p1
                            WHERE p1.kArtikel = ' . $kArtikel . '
                                AND (p1.kKundengruppe = 0 OR p1.kKundengruppe = ' . $kKundengruppe . ')
                                AND (p1.kKunde = ' . $kKunde . ' OR p1.kKunde IS NULL))';
        }
        $this->kArtikel      = $kArtikel;
        $this->kKundengruppe = $kKundengruppe;
        $this->kKunde        = $kKunde;

        $prices = $db->query(
            'SELECT *
                FROM tpreis AS p
                JOIN tpreisdetail AS d ON d.kPreis = p.kPreis
                WHERE p.kArtikel = ' . $kArtikel . ' ' . $filterKunde . '
                ORDER BY d.nAnzahlAb',
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($prices) > 0) {
            if ($kSteuerklasse === 0) {
                $tax           =
                    $db->select(
                        'tartikel',
                        'kArtikel',
                        $kArtikel,
                        null,
                        null,
                        null,
                        null,
                        false,
                        'kSteuerklasse'
                    );
                $kSteuerklasse = (int)$tax->kSteuerklasse;
            }
            $this->fUst        = Tax::getSalesTax($kSteuerklasse);
            $tmp               = $db->select(
                'tartikel',
                'kArtikel',
                $kArtikel,
                null,
                null,
                null,
                null,
                false,
                'fMwSt'
            );
            $defaultTax        = $tmp->fMwSt;
            $currentTax        = $this->fUst;
            $specialPriceValue = null;
            foreach ($prices as $i => $price) {
                // Kundenpreis?
                if ((int)$price->kKunde > 0) {
                    $this->Kundenpreis_aktiv = true;
                }
                // Standardpreis
                if ($price->nAnzahlAb < 1) {
                    $this->fVKNetto = $this->getRecalculatedNetPrice($price->fVKNetto, $defaultTax, $currentTax);
                    $specialPrice   = $db->queryPrepared(
                        "SELECT tsonderpreise.fNettoPreis, tartikelsonderpreis.dEnde AS dEnde_en,
                            DATE_FORMAT(tartikelsonderpreis.dEnde, '%d.%m.%Y') AS dEnde_de
                            FROM tsonderpreise
                            JOIN tartikel 
                                ON tartikel.kArtikel = :productID
                            JOIN tartikelsonderpreis 
                                ON tartikelsonderpreis.kArtikelSonderpreis = tsonderpreise.kArtikelSonderpreis
                                AND tartikelsonderpreis.kArtikel = :productID
                                AND tartikelsonderpreis.cAktiv = 'Y'
                                AND tartikelsonderpreis.dStart <= CURDATE()
                                AND (tartikelsonderpreis.dEnde IS NULL OR tartikelsonderpreis.dEnde >= CURDATE()) 
                                AND (tartikelsonderpreis.nAnzahl <= tartikel.fLagerbestand 
                                    OR tartikelsonderpreis.nIstAnzahl = 0)
                            WHERE tsonderpreise.kKundengruppe = :customerGroup",
                        [
                            'productID'     => $kArtikel,
                            'customerGroup' => $kKundengruppe,
                        ],
                        ReturnType::SINGLE_OBJECT
                    );

                    if (isset($specialPrice->fNettoPreis) && (double)$specialPrice->fNettoPreis < $this->fVKNetto) {
                        $specialPriceValue       = $this->getRecalculatedNetPrice(
                            $specialPrice->fNettoPreis,
                            $defaultTax,
                            $currentTax
                        );
                        $this->alterVKNetto      = $this->fVKNetto;
                        $this->fVKNetto          = $specialPriceValue;
                        $this->Sonderpreis_aktiv = 1;
                        $this->SonderpreisBis_de = $specialPrice->dEnde_de;
                        $this->SonderpreisBis_en = $specialPrice->dEnde_en;
                    }
                } else {
                    // Alte Preisstaffeln
                    if ($i <= 5) {
                        $scaleGetter = 'nAnzahl' . $i;
                        $priceGetter = 'fPreis' . $i;

                        $this->{$scaleGetter} = (int)$price->nAnzahlAb;
                        $this->{$priceGetter} = $specialPriceValue ?? $this->getRecalculatedNetPrice(
                            $price->fVKNetto,
                            $defaultTax,
                            $currentTax
                        );
                    }

                    $this->nAnzahl_arr[] = (int)$price->nAnzahlAb;
                    $this->fPreis_arr[]  =
                        ($specialPriceValue !== null && $specialPriceValue < (double)$price->fVKNetto)
                            ? $specialPriceValue
                            : $this->getRecalculatedNetPrice($price->fVKNetto, $defaultTax, $currentTax);
                }
            }
        }

        $this->berechneVKs();
        $this->oPriceRange = new PriceRange($kArtikel, $kKundengruppe, $kKunde);
        \executeHook(\HOOK_PRICES_CONSTRUCT, [
            'customerGroupID' => $kKundengruppe,
            'customerID'      => $kKunde,
            'productID'       => $kArtikel,
            'taxClassID'      => $kSteuerklasse,
            'prices'          => $this
        ]);
    }

    /**
     * Return recalculated new net price based on the rounded default gross price.
     * This is necessary for having consistent gross prices in case of
     * threshold delivery (Tax rate != default tax rate).
     *
     * @param double $netPrice      the product net price
     * @param double $defaultTax    the default tax factor of the product e.g. 19 for 19% vat
     * @param double $conversionTax the taxFactor of the delivery country / delivery threshold
     * @return double calculated net price based on a rounded(!!!) DEFAULT gross price.
     */
    private function getRecalculatedNetPrice($netPrice, $defaultTax, $conversionTax)
    {
        $newNetPrice = $netPrice;
        if (\CONSISTENT_GROSS_PRICES === true
            && $defaultTax > 0
            && $conversionTax > 0 &&
            $defaultTax != $conversionTax
        ) {
            $newNetPrice = \round($netPrice * ($defaultTax + 100) / 100, 2) / ($conversionTax + 100) * 100;
        }

        return (double)$newNetPrice;
    }

    /**
     * @param int $kKunde
     * @return bool
     */
    protected function hasCustomPrice(int $kKunde): bool
    {
        if ($kKunde > 0) {
            $cacheID = 'custprice_' . $kKunde;
            if (($oCustomPrice = Shop::Container()->getCache()->get($cacheID)) === false) {
                $oCustomPrice = Shop::Container()->getDB()->query(
                    'SELECT COUNT(kPreis) AS nAnzahl 
                        FROM tpreis
                        WHERE kKunde = ' . $kKunde,
                    ReturnType::SINGLE_OBJECT
                );
                if (\is_object($oCustomPrice)) {
                    $cacheTags = [\CACHING_GROUP_ARTICLE];
                    Shop::Container()->getCache()->set($cacheID, $oCustomPrice, $cacheTags);
                }
            }

            return \is_object($oCustomPrice) && $oCustomPrice->nAnzahl > 0;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isDiscountable(): bool
    {
        return !($this->Kundenpreis_aktiv || $this->Sonderpreis_aktiv);
    }

    /**
     * @param int $kKundengruppe
     * @param int $kArtikel
     * @return $this
     * @deprecated since 5.0.0 - removed tpreise
     */
    public function loadFromDB(int $kKundengruppe, int $kArtikel): self
    {
        trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

        return $this;
    }

    /**
     * @param float $Rabatt
     * @param float $offset
     * @return $this
     */
    public function rabbatierePreise($Rabatt, $offset = 0.0): self
    {
        if ($Rabatt != 0 && $this->isDiscountable()) {
            $this->rabatt       = $Rabatt;
            $this->alterVKNetto = $this->fVKNetto;

            $this->fVKNetto = ($this->fVKNetto - $this->fVKNetto * $Rabatt / 100.0) + $offset;
            $this->fPreis1  = ($this->fPreis1 - $this->fPreis1 * $Rabatt / 100.0) + $offset;
            $this->fPreis2  = ($this->fPreis2 - $this->fPreis2 * $Rabatt / 100.0) + $offset;
            $this->fPreis3  = ($this->fPreis3 - $this->fPreis3 * $Rabatt / 100.0) + $offset;
            $this->fPreis4  = ($this->fPreis4 - $this->fPreis4 * $Rabatt / 100.0) + $offset;
            $this->fPreis5  = ($this->fPreis5 - $this->fPreis5 * $Rabatt / 100.0) + $offset;

            foreach ($this->fPreis_arr as $i => $fPreis) {
                $this->fPreis_arr[$i] = ($fPreis - $fPreis * $Rabatt / 100.0) + $offset;
            }
            $this->berechneVKs();
            $this->oPriceRange->setDiscount($Rabatt);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function localizePreise(): self
    {
        $currency = Frontend::getCurrency();

        $this->cPreisLocalized_arr = [];
        foreach ($this->fPreis_arr as $fPreis) {
            $this->cPreisLocalized_arr[] = [
                self::getLocalizedPriceString(Tax::getGross($fPreis, $this->fUst, 4), $currency),
                self::getLocalizedPriceString($fPreis, $currency)
            ];
        }

        $this->cVKLocalized[0] = self::getLocalizedPriceString(
            Tax::getGross($this->fVKNetto, $this->fUst, 4),
            $currency
        );
        $this->cVKLocalized[1] = self::getLocalizedPriceString($this->fVKNetto, $currency);

        $this->fVKBrutto = Tax::getGross($this->fVKNetto, $this->fUst);

        if ($this->alterVKNetto) {
            $this->alterVKLocalized[0] = self::getLocalizedPriceString(
                Tax::getGross($this->alterVKNetto, $this->fUst, 4),
                $currency
            );
            $this->alterVKLocalized[1] = self::getLocalizedPriceString($this->alterVKNetto, $currency);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function berechneVKs(): self
    {
        $factor = Frontend::getCurrency()->getConversionFactor();

        $this->fVKBrutto = Tax::getGross($this->fVKNetto, $this->fUst);

        $this->fVK[0] = Tax::getGross($this->fVKNetto * $factor, $this->fUst);
        $this->fVK[1] = $this->fVKNetto * $factor;

        $this->alterVK[0] = Tax::getGross($this->alterVKNetto * $factor, $this->fUst);
        $this->alterVK[1] = $this->alterVKNetto * $factor;

        $this->fStaffelpreis_arr = [];
        foreach ($this->fPreis_arr as $fPreis) {
            $this->fStaffelpreis_arr[] = [
                Tax::getGross($fPreis * $factor, $this->fUst),
                $fPreis * $factor
            ];
        }

        return $this;
    }

    /**
     * @retun int
     * @deprecated since 5.0.0 - removed tpreise
     */
    public function insertInDB(): int
    {
        trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);

        return 0;
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }

    /**
     * @param int    $kKundengruppe
     * @param string $priceAlias
     * @param string $detailAlias
     * @param string $productAlias
     * @return string
     */
    public static function getPriceJoinSql(
        int $kKundengruppe,
        $priceAlias = 'tpreis',
        $detailAlias = 'tpreisdetail',
        $productAlias = 'tartikel'
    ): string {
        return 'JOIN tpreis AS ' . $priceAlias . ' ON ' . $priceAlias . '.kArtikel = ' . $productAlias . '.kArtikel
                    AND ' . $priceAlias . '.kKundengruppe = ' . $kKundengruppe . '
                JOIN tpreisdetail AS ' . $detailAlias . ' ON ' . $detailAlias . '.kPreis = ' . $priceAlias . '.kPreis
                    AND ' . $detailAlias . '.nAnzahlAb = 0';
    }

    /**
     * Set all fvk prices to zero.
     */
    public function setPricesToZero(): void
    {
        $this->fVKNetto  = 0;
        $this->fVKBrutto = 0;
        foreach ($this->fVK as $key => $fVK) {
            $this->fVK[$key] = 0;
        }
        foreach ($this->alterVK as $key => $alterVK) {
            $this->alterVK[$key] = 0;
        }
        $this->fPreis1 = 0;
        $this->fPreis2 = 0;
        $this->fPreis3 = 0;
        $this->fPreis4 = 0;
        $this->fPreis5 = 0;
        foreach ($this->fPreis_arr as $key => $fPreis) {
            $this->fPreis_arr[$key] = 0;
        }
        foreach ($this->fStaffelpreis_arr as $fStaffelpreisKey => $fStaffelpreis) {
            foreach ($fStaffelpreis as $fPreisKey => $fPreis) {
                $this->fStaffelpreis_arr[$fStaffelpreisKey][$fPreisKey] = 0;
            }
        }
    }

    /**
     * @param float    $preis
     * @param Currency $waehrung
     * @param bool     $html
     * @return string
     * @former gibPreisLocalizedOhneFaktor()
     */
    public static function getLocalizedPriceWithoutFactor($preis, $waehrung = null, bool $html = true): string
    {
        $currency = !$waehrung ? Frontend::getCurrency() : $waehrung;
        if ($currency !== null && \get_class($currency) === 'stdClass') {
            $currency = new Currency($currency->kWaehrung);
        }
        $localized    = \number_format($preis, 2, $currency->getDecimalSeparator(), $currency->getThousandsSeparator());
        $waherungname = $html ? $currency->getHtmlEntity() : $currency->getName();

        return $currency->getForcePlacementBeforeNumber()
            ? $waherungname . ' ' . $localized
            : $localized . ' ' . $waherungname;
    }

    /**
     * @param float       $price
     * @param object|null $currency
     * @param bool        $html
     * @param int         $decimals
     * @return string
     * @former self::getLocalizedPriceString()
     */
    public static function getLocalizedPriceString(
        $price,
        $currency = null,
        bool $html = true,
        int $decimals = 2
    ): string {
        if ($currency === null || \is_numeric($currency) || \is_bool($currency)) {
            $currency = Frontend::getCurrency();
        } elseif (\is_object($currency) && \get_class($currency) === 'stdClass') {
            $currency = new Currency((int)$currency->kWaehrung);
        }
        $localized    = \number_format(
            $price * $currency->getConversionFactor(),
            $decimals,
            $currency->getDecimalSeparator(),
            $currency->getThousandsSeparator()
        );
        $currencyName = $html ? $currency->getHtmlEntity() : $currency->getName();

        return $currency->getForcePlacementBeforeNumber()
            ? ($currencyName . ' ' . $localized)
            : ($localized . ' ' . $currencyName);
    }
}
