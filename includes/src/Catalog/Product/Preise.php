<?php

namespace JTL\Catalog\Product;

use JTL\Catalog\Currency;
use JTL\DB\DbInterface;
use JTL\Helpers\Tax;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class Preise
 * @package JTL\Catalog\Product
 */
class Preise
{
    /**
     * @var int
     */
    public int $kKundengruppe;

    /**
     * @var int
     */
    public int $kArtikel;

    /**
     * @var int
     */
    public int $kKunde;

    /**
     * @var array
     */
    public array $cVKLocalized = [];

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
    public array $alterVKLocalized = [];

    /**
     * @var array
     */
    public array $fVK = [];

    /**
     * @var array
     */
    public array $nAnzahl_arr = [];

    /**
     * @var array
     */
    public array $fPreis_arr = [];

    /**
     * @var array
     */
    public array $fStaffelpreis_arr = [];

    /**
     * @var array
     */
    public array $cPreisLocalized_arr = [];

    /**
     * @var bool|int
     */
    public $Sonderpreis_aktiv = false;

    /**
     * @var bool
     */
    public bool $Kundenpreis_aktiv = false;

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
     * @var int
     */
    public $discountPercentage = 0;

    /**
     * @var bool
     */
    public $noDiscount = false;

    /**
     * @var array
     */
    public array $cAufpreisLocalized = [];

    /**
     * @var array
     */
    public array $cPreisVPEWertInklAufpreis = [];

    /**
     * @var array - probably a typo? but it is used in templates..
     */
    public array $PreisecPreisVPEWertInklAufpreis = [];

    /**
     * @var DbInterface|null
     */
    private ?DbInterface $db = null;

    /**
     * Preise constructor.
     * @param int              $customerGroupID
     * @param int              $productID
     * @param int              $customerID
     * @param int              $taxClassID
     * @param DbInterface|null $db
     */
    public function __construct(
        int $customerGroupID,
        int $productID,
        int $customerID = 0,
        int $taxClassID = 0,
        ?DbInterface $db = null
    ) {
        $this->db            = $db ?? Shop::Container()->getDB();
        $this->kArtikel      = $productID;
        $this->kKundengruppe = $customerGroupID;
        $this->kKunde        = $customerID;

        $price   = $this->getPrice($customerGroupID, $productID, $customerID);
        $taxData = $this->getTaxData($productID);
        if ($price !== null && $taxData !== null) {
            if ($taxClassID === 0) {
                $taxClassID = (int)$taxData->kSteuerklasse;
            }
            if ((int)$price->kKunde > 0) {
                $this->Kundenpreis_aktiv = true;
            }
            if ((int)$price->noDiscount > 0) {
                $this->noDiscount = true;
            }
            $this->fUst        = Tax::getSalesTax($taxClassID);
            $defaultTax        = (int)$taxData->fMwSt;
            $currentTax        = $this->fUst;
            $specialPriceValue = null;
            $prices            = $this->getPriceDetails((int)$price->kPreis);
            foreach ($prices as $i => $price) {
                // Standardpreis
                if ($price->nAnzahlAb < 1) {
                    $this->fVKNetto = $this->getRecalculatedNetPrice($price->fVKNetto, $defaultTax, $currentTax);
                    $specialPrice   = $this->getSpecialPrice($customerGroupID, $productID);

                    if ($specialPrice !== null) {
                        $specialPrice->fNettoPreis = $this->getRecalculatedNetPrice(
                            $specialPrice->fNettoPreis ?? 0.0,
                            $defaultTax,
                            $currentTax
                        );
                        if ((float)$specialPrice->fNettoPreis < $this->fVKNetto) {
                            $specialPriceValue       = $specialPrice->fNettoPreis;
                            $this->alterVKNetto      = $this->fVKNetto;
                            $this->fVKNetto          = $specialPriceValue;
                            $this->Sonderpreis_aktiv = 1;
                            $this->SonderpreisBis_de = $specialPrice->dEnde_de;
                            $this->SonderpreisBis_en = $specialPrice->dEnde_en;
                        }
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
        $this->oPriceRange = new PriceRange($productID, $customerGroupID, $customerID, $this->db);
        \executeHook(\HOOK_PRICES_CONSTRUCT, [
            'customerGroupID' => $customerGroupID,
            'customerID'      => $customerID,
            'productID'       => $productID,
            'taxClassID'      => $taxClassID,
            'prices'          => $this
        ]);
    }

    /**
     * @param int $customerGroupID
     * @param int $productID
     * @param int $customerID
     * @return stdClass|null
     */
    protected function getPrice(
        int $customerGroupID,
        int $productID,
        int $customerID = 0
    ): ?stdClass {
        $params         = [
            'pid' => $productID,
            'cgid' => $customerGroupID
        ];
        $customerFilter = ' AND kKundengruppe = :cgid';
        if ($customerID > 0 && $this->hasCustomPrice($customerID)) {
            $params['cid']  = $customerID;
            $customerFilter = ' AND (p.kKundengruppe, COALESCE(p.kKunde, 0)) = (
                            SELECT min(IFNULL(p1.kKundengruppe, :cgid)), max(IFNULL(p1.kKunde, 0))
                            FROM tpreis AS p1
                            WHERE p1.kArtikel = :pid
                                AND (p1.kKundengruppe = 0 OR p1.kKundengruppe = :cgid)
                                AND (p1.kKunde = 0 OR p1.kKunde = :cid))';
        }

        return $this->db->getSingleObject(
            'SELECT kPreis, noDiscount, kKunde
                FROM tpreis AS p
                WHERE kArtikel = :pid' . $customerFilter,
            $params
        );
    }

    /**
     * @param int $priceID
     * @return array
     */
    protected function getPriceDetails(int $priceID): array
    {
        return $this->db->getObjects(
            'SELECT nAnzahlAb, fVKNetto
                FROM tpreisdetail
                WHERE kPreis = :priceID
                ORDER BY nAnzahlAb',
            ['priceID' => $priceID]
        );
    }

    /**
     * @param int $customerGroupID
     * @param int $productID
     * @return stdClass|null
     */
    protected function getSpecialPrice(int $customerGroupID, int $productID): ?stdClass
    {
        return $this->db->getSingleObject(
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
                'productID'     => $productID,
                'customerGroup' => $customerGroupID,
            ]
        );
    }

    /**
     * @param int $productID
     * @return stdClass|null
     */
    protected function getTaxData(int $productID): ?stdClass
    {
        return $this->db->getSingleObject(
            'SELECT kSteuerklasse, fMwSt
                FROM tartikel
                WHERE kArtikel = :pid',
            ['pid' => $productID]
        );
    }

    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface
    {
        if ($this->db === null || $this->db->isConnected() === false) {
            $this->db = Shop::Container()->getDB();
        }

        return $this->db;
    }

    /**
     * Return recalculated new net price based on the rounded default gross price.
     * This is necessary for having consistent gross prices in case of
     * threshold delivery (Tax rate != default tax rate).
     *
     * @param float|string $netPrice      the product net price
     * @param float|string $defaultTax    the default tax factor of the product e.g. 19 for 19% vat
     * @param float|string $conversionTax the taxFactor of the delivery country / delivery threshold
     * @return double - calculated net price based on a rounded(!!!) DEFAULT gross price.
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

        \executeHook(\HOOK_RECALCULATED_NET_PRICE, [
            'netPrice'      => $netPrice,
            'defaultTax'    => $defaultTax,
            'conversionTax' => $conversionTax,
            'newNetPrice'   => &$newNetPrice
        ]);

        return (double)$newNetPrice;
    }

    /**
     * @param int $customerID
     * @param int $productID
     * @return bool
     */
    public function customerHasCustomPriceForProduct(int $customerID, int $productID): bool
    {
        if (!$this->hasCustomPrice($customerID)) {
            return false;
        }

        return $this->getDB()->getSingleObject(
            'SELECT COUNT(kPreis) AS cnt 
                FROM tpreis
                WHERE kKunde = :cid 
                  AND (kArtikel = :pid OR kArtikel IN (SELECT kArtikel FROM tartikel WHERE kVaterArtikel = :pid))',
            ['cid' => $customerID, 'pid' => $productID]
        )->cnt > 0;
    }

    /**
     * @param int $customerID
     * @return bool
     */
    public function hasCustomPrice(int $customerID): bool
    {
        if ($customerID <= 0) {
            return false;
        }
        $cacheID = 'custprice_' . $customerID;
        if (($data = Shop::Container()->getCache()->get($cacheID)) === false) {
            $data = $this->getDB()->getSingleObject(
                'SELECT COUNT(kPreis) AS nAnzahl 
                    FROM tpreis
                    WHERE kKunde = :cid',
                ['cid' => $customerID]
            );
            if (\is_object($data)) {
                $cacheTags = [\CACHING_GROUP_ARTICLE];
                Shop::Container()->getCache()->set($cacheID, $data, $cacheTags);
            }
        }

        return $data !== null && $data->nAnzahl > 0;
    }

    /**
     * @return bool
     */
    public function isDiscountable(): bool
    {
        return !($this->Kundenpreis_aktiv || $this->Sonderpreis_aktiv || $this->noDiscount);
    }

    /**
     * @param float $discount
     * @param float $offset
     * @return $this
     */
    public function rabbatierePreise($discount, $offset = 0.0): self
    {
        if ($discount != 0 && $this->isDiscountable()) {
            $this->rabatt       = $discount;
            $this->alterVKNetto = $this->fVKNetto;

            $this->fVKNetto = ($this->fVKNetto - $this->fVKNetto * $discount / 100.0) + $offset;
            $this->fPreis1  = ($this->fPreis1 - $this->fPreis1 * $discount / 100.0) + $offset;
            $this->fPreis2  = ($this->fPreis2 - $this->fPreis2 * $discount / 100.0) + $offset;
            $this->fPreis3  = ($this->fPreis3 - $this->fPreis3 * $discount / 100.0) + $offset;
            $this->fPreis4  = ($this->fPreis4 - $this->fPreis4 * $discount / 100.0) + $offset;
            $this->fPreis5  = ($this->fPreis5 - $this->fPreis5 * $discount / 100.0) + $offset;

            foreach ($this->fPreis_arr as $i => $fPreis) {
                $this->fPreis_arr[$i] = ($fPreis - $fPreis * $discount / 100.0) + $offset;
            }
            $this->berechneVKs();
            $this->oPriceRange->setDiscount($discount);
        }

        return $this;
    }

    /**
     * @param Currency|null $currency
     * @return $this
     */
    public function localizePreise(?Currency $currency = null): self
    {
        $currency                  = self::getCurrency($currency);
        $this->cPreisLocalized_arr = [];
        foreach ($this->fPreis_arr as $price) {
            $this->cPreisLocalized_arr[] = [
                self::getLocalizedPriceString(Tax::getGross($price, $this->fUst, 4), $currency),
                self::getLocalizedPriceString($price, $currency)
            ];
        }
        $this->cVKLocalized[0] = self::getLocalizedPriceString(
            Tax::getGross($this->fVKNetto, $this->fUst, 4),
            $currency
        );
        $this->cVKLocalized[1] = self::getLocalizedPriceString($this->fVKNetto, $currency);
        $this->fVKBrutto       = Tax::getGross($this->fVKNetto, $this->fUst);
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
        foreach ($this->fPreis_arr as $price) {
            $this->fStaffelpreis_arr[] = [
                Tax::getGross($price * $factor, $this->fUst),
                $price * $factor
            ];
        }
        if (!empty($this->alterVKNetto)) {
            $this->discountPercentage = (int)\round(
                (($this->alterVKNetto - $this->fVKNetto) * 100) / $this->alterVKNetto
            );
        }

        return $this;
    }

    /**
     * @param int    $customerGroupID
     * @param string $priceAlias
     * @param string $detailAlias
     * @param string $productAlias
     * @return string
     */
    public static function getPriceJoinSql(
        int $customerGroupID,
        string $priceAlias = 'tpreis',
        string $detailAlias = 'tpreisdetail',
        string $productAlias = 'tartikel'
    ): string {
        return 'JOIN tpreis AS ' . $priceAlias . ' ON ' . $priceAlias . '.kArtikel = ' . $productAlias . '.kArtikel
                    AND ' . $priceAlias . '.kKundengruppe = ' . $customerGroupID . '
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
        foreach ($this->fPreis_arr as $key => $price) {
            $this->fPreis_arr[$key] = 0;
        }
        foreach ($this->fStaffelpreis_arr as $fStaffelpreisKey => $fStaffelpreis) {
            foreach ($fStaffelpreis as $pKey => $price) {
                $this->fStaffelpreis_arr[$fStaffelpreisKey][$pKey] = 0;
            }
        }
    }

    /**
     * @param float|string           $price
     * @param Currency|stdClass|null $currency
     * @param bool                   $html
     * @return string
     * @former gibPreisLocalizedOhneFaktor()
     */
    public static function getLocalizedPriceWithoutFactor($price, $currency = null, bool $html = true): string
    {
        $currency = $currency ?? Frontend::getCurrency();
        if ($currency !== null && \get_class($currency) === 'stdClass') {
            $currency = new Currency($currency->kWaehrung);
        }
        $localized = \number_format($price, 2, $currency->getDecimalSeparator(), $currency->getThousandsSeparator());
        $name      = $html ? $currency->getHtmlEntity() : $currency->getName();

        return $currency->getForcePlacementBeforeNumber()
            ? $name . ' ' . $localized
            : $localized . ' ' . $name;
    }

    /**
     * @param float|string $price
     * @param mixed        $currency
     * @param bool         $html
     * @param int          $decimals
     * @return string
     * @former self::getLocalizedPriceString()
     */
    public static function getLocalizedPriceString(
        $price,
        $currency = null,
        bool $html = true,
        int $decimals = 2
    ): string {
        $currency     = self::getCurrency($currency);
        $localized    = \number_format(
            $price * $currency->getConversionFactor(),
            $decimals,
            $currency->getDecimalSeparator(),
            $currency->getThousandsSeparator()
        );
        $currencyName = $html ? $currency->getHtmlEntity() : $currency->getName();

        \executeHook(\HOOK_LOCALIZED_PRICE_STRING, [
            'price'        => $price,
            'currency'     => &$currency,
            'html'         => $html,
            'decimals'     => $decimals,
            'currencyName' => &$currencyName,
            'localized'    => &$localized
        ]);

        return $currency->getForcePlacementBeforeNumber()
            ? ($currencyName . ' ' . $localized)
            : ($localized . ' ' . $currencyName);
    }

    /**
     * @param mixed $currency
     * @return Currency
     */
    private static function getCurrency($currency): Currency
    {
        if ($currency instanceof Currency) {
            return $currency;
        }
        if ($currency === null || \is_numeric($currency) || \is_bool($currency)) {
            $currency = Frontend::getCurrency();
        } elseif ($currency instanceof stdClass) {
            $loaded = null;
            foreach (Frontend::getCurrencies() as $cur) {
                if ($cur->getID() === (int)$currency->kWaehrung) {
                    $loaded = $cur;
                    break;
                }
            }
            $currency = $loaded ?? new Currency((int)$currency->kWaehrung);
        } elseif (\is_string($currency)) {
            $currency = Currency::fromISO($currency);
        } else {
            $currency = new Currency();
        }

        return $currency;
    }
}
