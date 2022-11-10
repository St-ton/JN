<?php declare(strict_types=1);

namespace JTL\Catalog\Product;

use JTL\DB\DbInterface;
use JTL\Helpers\Tax;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class PriceRange
 * @package JTL\Catalog\Product
 */
class PriceRange
{
    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var stdClass
     */
    private stdClass $productData;

    /**
     * @var int
     */
    private int $customerGroupID;

    /**
     * @var int
     */
    private int $customerID;

    /**
     * @var float|int
     */
    private int|float $discount = 0;

    /**
     * @var array
     */
    private static array $calculatedRange = [];

    /**
     * @var float
     */
    public float $minNettoPrice = 0.0;

    /**
     * @var float
     */
    public float $maxNettoPrice = 0.0;

    /**
     * @var float
     */
    public float $minBruttoPrice = 0.0;

    /**
     * @var float
     */
    public float $maxBruttoPrice = 0.0;

    /**
     * @var bool
     */
    public bool $isMinSpecialPrice = false;

    /**
     * @var bool
     */
    public bool $isMaxSpecialPrice = false;

    /**
     * PriceRange constructor.
     *
     * @param int              $productID
     * @param int              $customerGroupID
     * @param int              $customerID
     * @param DbInterface|null $db
     */
    public function __construct(int $productID, int $customerGroupID = 0, int $customerID = 0, ?DbInterface $db = null)
    {
        $this->db        = $db ?? Shop::Container()->getDB();
        $customerGroupID = $customerGroupID ?: Frontend::getCustomerGroup()->getID();
        $customerID      = $customerID ?: Frontend::getCustomer()->getID();

        $this->productData     = (object)[
            'kArtikel'      => 0,
            'kSteuerklasse' => 0,
            'fLagerbestand' => 0,
            'kKonfiggruppe' => 0,
            'fNettoPreis'   => 0.0,
        ];
        $this->customerGroupID = $customerGroupID;
        $this->customerID      = $customerID;

        $productData = $this->getProduct($productID);
        if ($productData !== null) {
            $this->productData->kArtikel      = (int)$productData->kArtikel;
            $this->productData->kSteuerklasse = (int)$productData->kSteuerklasse;
            $this->productData->fLagerbestand = (float)$productData->fLagerbestand;
            $this->productData->fNettoPreis   = (float)$productData->fNettoPreis;
            $this->productData->kKonfiggruppe = 0;
            if ($productData->g1 !== null && $productData->g1 === $productData->g2) {
                $this->productData->kKonfiggruppe = (int)$productData->g1;
            }
            if ((int)$productData->nIstVater > 0
                || (int)$productData->kArtikelVariation > 0
                || $this->productData->kKonfiggruppe > 0
            ) {
                $key = $this->productData->kArtikel . ':' . $this->customerGroupID . $this->customerID;
                if ($this->hasStaticCache($key)) {
                    $this->loadStaticCache($key);
                } else {
                    $this->loadPriceRange();
                    $this->storeStaticCache($key);
                }
            } else {
                $ust = Tax::getSalesTax($this->productData->kSteuerklasse);

                $this->minNettoPrice  = $this->productData->fNettoPreis;
                $this->maxNettoPrice  = $this->productData->fNettoPreis;
                $this->minBruttoPrice = Tax::getGross($this->minNettoPrice, $ust, 4);
                $this->maxBruttoPrice = Tax::getGross($this->maxNettoPrice, $ust, 4);
            }
        }
    }

    /**
     * @param string $key
     * @return void
     */
    private function storeStaticCache(string $key): void
    {
        self::$calculatedRange[$key] = [
            'minNetto'  => $this->minNettoPrice,
            'maxNetto'  => $this->maxNettoPrice,
            'minBrutto' => $this->minBruttoPrice,
            'maxBrutto' => $this->maxBruttoPrice,
        ];
    }

    /**
     * @param string $key
     * @return void
     */
    private function loadStaticCache(string $key): void
    {
        $this->minNettoPrice  = self::$calculatedRange[$key]['minNetto'];
        $this->maxNettoPrice  = self::$calculatedRange[$key]['maxNetto'];
        $this->minBruttoPrice = self::$calculatedRange[$key]['minBrutto'];
        $this->maxBruttoPrice = self::$calculatedRange[$key]['maxBrutto'];
    }

    /**
     * @param string $key
     * @return bool
     */
    private function hasStaticCache(string $key): bool
    {
        return isset(self::$calculatedRange[$key]);
    }

    /**
     * @param int $productID
     * @return stdClass|null
     */
    private function getProduct(int $productID): ?stdClass
    {
        return $this->db->getSingleObject(
            'SELECT tartikel.kArtikel, tartikel.nIstVater, COALESCE(teigenschaft.kArtikel, 0) AS kArtikelVariation,
                tartikel.kSteuerklasse, tartikel.fLagerbestand,
                tartikel.fStandardpreisNetto fNettoPreis,
                tartikelkonfiggruppe.kKonfiggruppe g1, tkonfigitem.kKonfiggruppe g2
                FROM tartikel
                LEFT JOIN tartikelkonfiggruppe
                    ON tartikelkonfiggruppe.kArtikel = tartikel.kArtikel
                LEFT JOIN tkonfigitem
                    ON tkonfigitem.kKonfiggruppe = tartikelkonfiggruppe.kKonfiggruppe
                LEFT JOIN teigenschaft
                    ON teigenschaft.kArtikel = tartikel.kArtikel
                WHERE tartikel.kArtikel = :pid',
            ['pid' => $productID]
        );
    }

    /**
     * @param int $productID
     * @param int $customerGroupID
     * @param int $customerID
     * @return stdClass|null
     */
    private function getPriceRange(int $productID, int $customerGroupID = 0, int $customerID = 0): ?stdClass
    {
        return $this->db->getSingleObject(
            "SELECT baseprice.kArtikel,
                    MIN(IF(varaufpreis.fMinAufpreisNetto IS NULL,
                        ROUND(COALESCE(baseprice.specialPrice, 999999999), 4),
                        ROUND(baseprice.specialPrice, 4) + ROUND(varaufpreis.fMinAufpreisNetto, 4))) specialPriceMin,
                    MAX(IF(varaufpreis.fMaxAufpreisNetto IS NULL,
                        ROUND(COALESCE(baseprice.specialPrice, 0), 4),
                        ROUND(baseprice.specialPrice, 4) + ROUND(varaufpreis.fMaxAufpreisNetto, 4))) specialPriceMax,
                    MIN(IF(varaufpreis.fMinAufpreisNetto IS NULL,
                        ROUND(baseprice.fVKNetto, 4),
                        ROUND(baseprice.fVKNetto, 4) + ROUND(varaufpreis.fMinAufpreisNetto, 4))
                    ) fVKNettoMin,
                    MAX(IF(varaufpreis.fMaxAufpreisNetto IS NULL,
                        ROUND(baseprice.fVKNetto, 4),
                        ROUND(baseprice.fVKNetto, 4) + ROUND(varaufpreis.fMaxAufpreisNetto, 4))
                    ) fVKNettoMax
            FROM (
                SELECT IF(tartikel.kVaterartikel = 0, tartikel.kArtikel, tartikel.kVaterartikel) kArtikel,
                       tartikel.kArtikel kKindArtikel,
                       tartikel.nIstVater,
                       tsonderpreise.fNettoPreis specialPrice,
                       IF(tsonderpreise.fNettoPreis < tpreisdetail.fVKNetto,
                          tsonderpreise.fNettoPreis, tpreisdetail.fVKNetto) fVKNetto
                FROM tartikel
                INNER JOIN tpreis ON tpreis.kArtikel = tartikel.kArtikel
                INNER JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                LEFT JOIN  tartikelsonderpreis ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                LEFT JOIN  tsonderpreise
                    ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                       AND tsonderpreise.kKundengruppe = tpreis.kKundengruppe
                       AND tartikelsonderpreis.cAktiv = 'Y'
                       AND tartikelsonderpreis.dStart <= CURDATE()
                       AND (tartikelsonderpreis.nIstAnzahl = 0 
                           OR (tartikelsonderpreis.nAnzahl <= tartikel.fLagerbestand))
                       AND (tartikelsonderpreis.nIstDatum = 0
                           OR (tartikelsonderpreis.dEnde >= CURDATE()))
                WHERE tartikel.nIstVater = 0
                  AND ((tpreis.kKundengruppe = 0 AND tpreis.kKunde = :customerID)
                    OR (tpreis.kKundengruppe = :customerGroup AND NOT EXISTS(
                        SELECT 1 FROM tpreis iPrice
                            WHERE iPrice.kKunde = :customerID
                                AND iPrice.kKundengruppe = 0
                                AND iPrice.kArtikel = tartikel.kArtikel
                        )))
                  AND (
                    (tartikel.kVaterartikel = 0 AND tartikel.kArtikel = :productID)
                        OR tartikel.kVaterartikel = :productID
                  )
            ) baseprice
            LEFT JOIN (
                SELECT variations.kArtikel, variations.kKundengruppe,
                     SUM(variations.fMinAufpreisNetto) fMinAufpreisNetto,
                     SUM(variations.fMaxAufpreisNetto) fMaxAufpreisNetto
                FROM (
                  SELECT teigenschaft.kArtikel,
                         tkundengruppe.kKundengruppe,
                         MIN(COALESCE(teigenschaftwertaufpreis.fAufpreisNetto,
                            teigenschaftwert.fAufpreisNetto)) fMinAufpreisNetto,
                         MAX(COALESCE(teigenschaftwertaufpreis.fAufpreisNetto,
                            teigenschaftwert.fAufpreisNetto)) fMaxAufpreisNetto
                  FROM teigenschaft
                  INNER JOIN teigenschaftwert ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                  JOIN  tkundengruppe
                  LEFT JOIN teigenschaftwertaufpreis
                      ON teigenschaftwertaufpreis.kEigenschaftWert = teigenschaftwert.kEigenschaftWert
                      AND teigenschaftwertaufpreis.kKundengruppe = tkundengruppe.kKundengruppe
                  WHERE teigenschaft.kArtikel = :productID
                  GROUP BY teigenschaft.kArtikel, tkundengruppe.kKundengruppe, teigenschaft.kEigenschaft
                ) variations
                GROUP BY variations.kArtikel, variations.kKundengruppe
            ) varaufpreis
                ON varaufpreis.kArtikel = baseprice.kKindArtikel
                  AND varaufpreis.kKundengruppe = :customerGroup
                  AND baseprice.nIstVater = 0
            GROUP BY baseprice.kArtikel",
            [
                'productID'     => $productID,
                'customerGroup' => $customerGroupID,
                'customerID'    => $customerID,
            ]
        );
    }

    /**
     * @return void
     */
    private function loadPriceRange(): void
    {
        $priceRange = $this->getPriceRange(
            (int)$this->productData->kArtikel,
            $this->customerGroupID,
            $this->customerID
        );
        if ($priceRange !== null) {
            $roundedMin              = \round((float)($priceRange->specialPriceMin ?? 0), 2);
            $roundedMax              = \round((float)($priceRange->specialPriceMax ?? 0), 2);
            $this->minNettoPrice     = (float)$priceRange->fVKNettoMin;
            $this->maxNettoPrice     = (float)$priceRange->fVKNettoMax;
            $this->isMinSpecialPrice = $roundedMin === \round($this->minNettoPrice, 2);
            $this->isMaxSpecialPrice = $roundedMax === \round($this->maxNettoPrice, 2);
        } else {
            $this->minNettoPrice = $this->productData->fNettoPreis;
            $this->maxNettoPrice = $this->productData->fNettoPreis;
        }

        if ($this->productData->kKonfiggruppe > 0) {
            $this->loadConfiguratorRange();
        }

        $ust = Tax::getSalesTax($this->productData->kSteuerklasse);

        $this->minBruttoPrice = Tax::getGross($this->minNettoPrice, $ust, 4);
        $this->maxBruttoPrice = Tax::getGross($this->maxNettoPrice, $ust, 4);
    }

    /**
     * @return void
     */
    private function loadConfiguratorRange(): void
    {
        $configItems = $this->db->getObjects(
            'SELECT tartikel.kArtikel,
                    tkonfiggruppe.kKonfiggruppe,
                    MIN(tkonfiggruppe.nMin) AS nMin,
                    MAX(tkonfiggruppe.nMax) AS nMax,
                    tkonfigitem.kArtikel kKindArtikel,
                    tkonfigitem.bPreis,
                    MIN(tkonfigitem.fMin) AS fMin,
                    MAX(tkonfigitem.fMax) AS fMax,
                    IF(tkonfigitem.bPreis = 0, tkonfigitempreis.kSteuerklasse, tartikel.kSteuerklasse) AS kSteuerklasse,
                    MIN(tkonfigitempreis.fPreis) fMinPreis,
                    Max(tkonfigitempreis.fPreis) fMaxPreis
                FROM tartikel
                INNER JOIN tartikelkonfiggruppe ON tartikelkonfiggruppe.kArtikel = tartikel.kArtikel
                INNER JOIN tkonfiggruppe ON tkonfiggruppe.kKonfiggruppe = tartikelkonfiggruppe.kKonfiggruppe
                INNER JOIN tkonfigitem ON tkonfigitem.kKonfiggruppe = tartikelkonfiggruppe.kKonfiggruppe
                LEFT JOIN tkonfigitempreis ON tkonfigitempreis.kKonfigitem = tkonfigitem.kKonfigitem
                    AND tkonfigitempreis.kKundengruppe = :customerGroup
                WHERE tartikel.kArtikel = :productID
                GROUP BY tartikel.kArtikel,
                    tkonfiggruppe.kKonfiggruppe,
                    tkonfigitem.kArtikel,
                    tkonfigitem.bPreis,
                    IF(tkonfigitem.bPreis = 0, tkonfigitempreis.kSteuerklasse, tartikel.kSteuerklasse)',
            [
                'productID'     => $this->productData->kArtikel,
                'customerGroup' => $this->customerGroupID,
            ]
        );

        $configGroups = [];
        foreach ($configItems as $configItem) {
            $configItem->kArtikel      = (int)$configItem->kArtikel;
            $configItem->kKonfiggruppe = (int)$configItem->kKonfiggruppe;
            $configItem->kSteuerklasse = (int)$configItem->kSteuerklasse;
            $configItem->nMin          = (int)$configItem->nMin;
            $configItem->nMax          = (int)$configItem->nMax;
            $configItemID              = $configItem->kKonfiggruppe;
            if (!isset($configGroups[$configItemID])) {
                $configGroups[$configItemID] = (object)[
                    'nMin'   => $configItem->nMin,
                    'nMax'   => $configItem->nMax,
                    'prices' => (object)[
                        'min' => [],
                        'max' => [],
                    ],
                ];
            }

            $ust = Tax::getSalesTax($configItem->kSteuerklasse);

            if ((int)$configItem->bPreis === 0) {
                $configGroups[$configItemID]->prices->min[] =
                    (float)$configItem->fMin * Tax::getGross((float)$configItem->fMinPreis, $ust, 4);
                $configGroups[$configItemID]->prices->max[] =
                    (float)$configItem->fMax * Tax::getGross((float)$configItem->fMaxPreis, $ust, 4);
            } else {
                $priceRange = new PriceRange(
                    (int)$configItem->kKindArtikel,
                    $this->customerGroupID,
                    $this->customerID,
                    $this->db
                );
                // Es wird immer maxNettoPrice verwendet, da im Konfigurator keine Staffelpreise berücksichtigt werden
                $configGroups[$configItemID]->prices->min[] =
                    (float)$configItem->fMin * Tax::getGross($priceRange->maxNettoPrice, $ust, 4);
                $configGroups[$configItemID]->prices->max[] =
                    (float)$configItem->fMax * Tax::getGross($priceRange->maxNettoPrice, $ust, 4);
            }
        }

        $minPrices = [];
        $maxPrices = [];

        foreach ($configGroups as $configGroup) {
            \sort($configGroup->prices->min);
            \rsort($configGroup->prices->max);
            $minPrice = 0;
            $maxPrice = 0;

            // Für den kleinsten Preis werden zuerst alle kleinsten Preise bis zur Mindestanzahl addiert...
            foreach (\array_slice($configGroup->prices->min, 0, $configGroup->nMin) as $price) {
                $minPrice += $price;
            }
            // ...und zusätzlich - bis zur Maximalanzahl - alle Preise < 0, also alle Abschläge
            foreach (\array_slice(
                $configGroup->prices->min,
                $configGroup->nMin,
                $configGroup->nMax - $configGroup->nMin
            ) as $price) {
                if ($price < 0) {
                    $minPrice += $price;
                }
            }

            // Für den größten Preis werden zuerst alle größten Preise bis zur Mindestanzahl addiert...
            foreach (\array_slice($configGroup->prices->max, 0, $configGroup->nMin) as $price) {
                $maxPrice += $price;
            }
            // ...und danach - bis zur Maximalanzahl - nur noch Preise > 0, also keine Abschläge
            foreach (\array_slice(
                $configGroup->prices->max,
                $configGroup->nMin,
                $configGroup->nMax - $configGroup->nMin
            ) as $price) {
                if ($price > 0) {
                    $maxPrice += $price;
                }
            }

            $minPrices[] = $minPrice;
            $maxPrices[] = $maxPrice;
        }

        $ust = Tax::getSalesTax($this->productData->kSteuerklasse);

        // Die jeweiligen Min- und Maxpreise sind die Summen aus allen Konfig-Gruppen
        $this->minNettoPrice += Tax::getNet(\array_sum($minPrices), $ust, 4);
        $this->maxNettoPrice += Tax::getNet(\array_sum($maxPrices), $ust, 4);
    }

    /**
     * @param float $discount
     * @return void
     */
    public function setDiscount(float $discount): void
    {
        $discount /= 100;
        if ($discount !== $this->discount) {
            if (!$this->isMinSpecialPrice) {
                $this->minNettoPrice /= (1 - $this->discount);
            }
            if (!$this->isMaxSpecialPrice) {
                $this->maxNettoPrice /= (1 - $this->discount);
            }

            $this->discount = $discount;

            $ust = Tax::getSalesTax($this->productData->kSteuerklasse);

            if (!$this->isMinSpecialPrice) {
                $this->minNettoPrice *= (1 - $this->discount);
            }
            if (!$this->isMaxSpecialPrice) {
                $this->maxNettoPrice *= (1 - $this->discount);
            }
            $this->minBruttoPrice = Tax::getGross($this->minNettoPrice, $ust, 4);
            $this->maxBruttoPrice = Tax::getGross($this->maxNettoPrice, $ust, 4);
        }
    }

    /**
     * return
     *      true - if min price is lower than max price
     *      else - otherwise
     *
     * @return bool
     */
    public function isRange(): bool
    {
        return \round($this->minNettoPrice, 2) < \round($this->maxNettoPrice, 2);
    }

    /**
     * get range width in percent
     *
     * @return float|int
     */
    public function rangeWidth(): float|int
    {
        return (int)$this->minNettoPrice !== 0
            ? 100 / $this->minNettoPrice * $this->maxNettoPrice - 100
            : 0;
    }

    /**
     * get localized min - max prices as array
     *
     * @param int|null $netto
     * @return array
     */
    public function getLocalizedArray(int $netto = null): array
    {
        if ($netto !== null) {
            return $netto === 0
                ? [ $this->getMinLocalized(0) , $this->getMaxLocalized(0) ]
                : [ $this->getMinLocalized(1) , $this->getMaxLocalized(1) ];
        }

        return [
            [ $this->getMinLocalized(0) , $this->getMaxLocalized(0) ],
            [ $this->getMinLocalized(1) , $this->getMaxLocalized(1) ]
        ];
    }

    /**
     * get localized min strings
     *
     * @param int|null $netto
     * @return string|string[]
     */
    public function getMinLocalized(?int $netto = null): array|string
    {
        $currency = Frontend::getCurrency();

        if ($netto !== null) {
            return $netto === 0
                ? Preise::getLocalizedPriceString($this->minBruttoPrice, $currency)
                : Preise::getLocalizedPriceString($this->minNettoPrice, $currency);
        }

        return [
            Preise::getLocalizedPriceString($this->minBruttoPrice, $currency),
            Preise::getLocalizedPriceString($this->minNettoPrice, $currency),
        ];
    }

    /**
     * get localized max strings
     *
     * @param int|null $netto
     * @return string|string[]
     */
    public function getMaxLocalized(?int $netto = null): array|string
    {
        $currency = Frontend::getCurrency();

        if ($netto !== null) {
            return $netto === 0
                ? Preise::getLocalizedPriceString($this->maxBruttoPrice, $currency)
                : Preise::getLocalizedPriceString($this->maxNettoPrice, $currency);
        }

        return [
            Preise::getLocalizedPriceString($this->maxBruttoPrice, $currency),
            Preise::getLocalizedPriceString($this->maxNettoPrice, $currency),
        ];
    }

    /**
     * get product data
     *
     * @return stdClass
     */
    public function getProductData(): stdClass
    {
        return $this->productData;
    }
}
