<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 * @package       jtl-shop
 * @since         5.0
 */

use DB\ReturnType;

/**
 * Class PriceRange
 */
class PriceRange
{
    /**
     * @var stdClass
     */
    private $articleData;

    /**
     * @var int
     */
    private $customerGroupID;

    /**
     * @var int
     */
    private $customerID;

    /**
     * @var int
     */
    private $discount;

    /**
     * @var float
     */
    public $minNettoPrice;

    /**
     * @var float
     */
    public $maxNettoPrice;

    /**
     * @var float
     */
    public $minBruttoPrice;

    /**
     * @var float
     */
    public $maxBruttoPrice;

    /**
     * PriceRange constructor.
     * @param int $articleID
     * @param int $customerGroupID
     * @param int $customerID
     */
    public function __construct(int $articleID, int $customerGroupID = 0, int $customerID = 0)
    {
        if ($customerGroupID === 0) {
            $customerGroupID = Session::CustomerGroup()->getID();
        }

        if ($customerID === 0) {
            $customerID = Session::Customer()->kKunde ?? 0;
        }

        $this->customerGroupID = $customerGroupID;
        $this->customerID      = $customerID;
        $this->discount        = 0;
        $this->articleData     = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $articleID,
            null,
            null,
            null,
            null,
            false,
            'kArtikel, kSteuerklasse, fLagerbestand, fStandardpreisNetto fNettoPreis'
        );

        $this->loadPriceRange();
    }

    /**
     * load price range from database
     * @return void
     */
    private function loadPriceRange()
    {
        $priceRange = Shop::Container()->getDB()->queryPrepared(
            'SELECT fVKNettoMin, fVKNettoMax 
                FROM tpreisrange
                WHERE kArtikel = :articleID
                    AND (
                        (kKundengruppe = 0 AND kKunde = :customerID)
                        OR
                        (kKundengruppe = :customerGroup AND (
                            nLagerAnzahlMax IS NULL OR (nLagerAnzahlMax <= :stock AND dStart <= NOW())
                            OR
                            (dStart IS NULL AND dEnde IS NULL)
                            OR
                            (NOW() BETWEEN dStart AND dEnde)
                        ))
                    )
                ORDER BY nRangeType ASC LIMIT 1',
            [
                'articleID'     => $this->articleData->kArtikel,
                'customerGroup' => $this->customerGroupID,
                'customerID'    => $this->customerID,
                'stock'         => $this->articleData->fLagerbestand,
            ],
            ReturnType::SINGLE_OBJECT
        );

        if ($priceRange) {
            $this->minNettoPrice = (float)$priceRange->fVKNettoMin;
            $this->maxNettoPrice = (float)$priceRange->fVKNettoMax;
        } else {
            $this->minNettoPrice = $this->articleData->fNettoPreis;
            $this->maxNettoPrice = $this->articleData->fNettoPreis;
        }

        if (class_exists('Konfigurator') && Konfigurator::hasKonfig($this->articleData->kArtikel)) {
            $this->loadConfiguratorRange();
        }

        $ust = gibUst($this->articleData->kSteuerklasse);

        $this->minBruttoPrice = berechneBrutto($this->minNettoPrice, $ust);
        $this->maxBruttoPrice = berechneBrutto($this->maxNettoPrice, $ust);
    }

    public function loadConfiguratorRange()
    {
        $configItems = Shop::Container()->getDB()->queryPrepared(
            "SELECT tartikel.kArtikel,
	                tkonfiggruppe.kKonfiggruppe,
                    MIN(tkonfiggruppe.nMin) nMin,
                    MAX(tkonfiggruppe.nMax) nMax,
                    tkonfigitem.kArtikel kKindArtikel,
                    tkonfigitem.bPreis,
                    MIN(tkonfigitem.fMin) fMin,
                    MAX(tkonfigitem.fMax) fMax,
                    IF(tkonfigitem.bPreis = 0, tkonfigitempreis.kSteuerklasse, tartikel.kSteuerklasse) kSteuerklasse,
                    MIN(tkonfigitempreis.fPreis) fMinPreis,
                    Max(tkonfigitempreis.fPreis) fMaxPreis
                FROM tartikel
                INNER JOIN tartikelkonfiggruppe ON tartikelkonfiggruppe.kArtikel = tartikel.kArtikel
                INNER JOIN tkonfiggruppe ON tkonfiggruppe.kKonfiggruppe = tartikelkonfiggruppe.kKonfiggruppe
                INNER JOIN tkonfigitem ON tkonfigitem.kKonfiggruppe = tartikelkonfiggruppe.kKonfiggruppe
                INNER JOIN tartikel tkonfigartikel ON tkonfigartikel.kArtikel = tkonfigitem.kArtikel
                LEFT JOIN tkonfigitempreis ON tkonfigitempreis.kKonfigitem = tkonfigitem.kKonfigitem
                WHERE tartikel.kArtikel = :articleID
                    AND tkonfigitempreis.kKundengruppe = :customerGroup
                GROUP BY tartikel.kArtikel,
	                tkonfiggruppe.kKonfiggruppe,
	                tkonfigitem.kArtikel,
	                tkonfigitem.bPreis,
                    IF(tkonfigitem.bPreis = 0, tkonfigitempreis.kSteuerklasse, tartikel.kSteuerklasse)",
            [
                'articleID'     => $this->articleData->kArtikel,
                'customerGroup' => $this->customerGroupID,
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );

        $configGroups = [];
        foreach ($configItems as $configItem) {
            $configItemID = (int)$configItem->kKonfiggruppe;
            if (!isset($configGroups[$configItemID])) {
                $configGroups[$configItemID] = (object)[
                    'nMin'   => (int)$configItem->nMin,
                    'nMax'   => (int)$configItem->nMax,
                    'prices' => (object)[
                        'min' => [],
                        'max' => [],
                    ],
                ];
            }

            $ust = gibUst($configItem->kSteuerklasse);

            if ((int)$configItem->bPreis === 0) {
                $configGroups[$configItemID]->prices->min[] = (float)$configItem->fMin * berechneBrutto((float)$configItem->fMinPreis, $ust, 4);
                $configGroups[$configItemID]->prices->max[] = (float)$configItem->fMax * berechneBrutto((float)$configItem->fMaxPreis, $ust, 4);
            } else {
                $priceRange = new PriceRange((int)$configItem->kKindArtikel, $this->customerGroupID, $this->customerID);
                // Es wird immer maxNettoPrice verwendet, da im Konfigurator keine Staffelpreise berücksichtigt werden
                $configGroups[$configItemID]->prices->min[] = (float)$configItem->fMin * berechneBrutto($priceRange->maxNettoPrice, $ust, 4);
                $configGroups[$configItemID]->prices->max[] = (float)$configItem->fMax * berechneBrutto($priceRange->maxNettoPrice, $ust, 4);
            }
        }

        $minPrices = [];
        $maxPrices = [];

        foreach ($configGroups as $configGroup) {
            sort($configGroup->prices->min);
            rsort($configGroup->prices->max);
            $minPrice = 0;
            $maxPrice = 0;

            // Für den kleinsten Preis werden zuerst alle kleinsten Preise bis zur Mindestanzahl addiert...
            foreach (array_slice($configGroup->prices->min, 0, $configGroup->nMin) as $price) {
                $minPrice += $price;
            }
            // ...und zusätzlich - bis zur Maximalanzahl - alle Preise < 0, also alle Abschläge
            foreach (array_slice($configGroup->prices->min, $configGroup->nMin, $configGroup->nMax - $configGroup->nMin) as $price) {
                if ($price < 0) {
                    $minPrice += $price;
                }
            }

            // Für den größten Preis werden zuerst alle größten Preise bis zur Mindestanzahl addiert...
            foreach (array_slice($configGroup->prices->max, 0, $configGroup->nMin) as $price) {
                $maxPrice += $price;
            }
            // ...und danach - bis zur Maximalanzahl - nur noch Preise > 0, also keine Abschläge
            foreach (array_slice($configGroup->prices->max, $configGroup->nMin, $configGroup->nMax - $configGroup->nMin) as $price) {
                if ($price > 0) {
                    $maxPrice += $price;
                }
            }

            $minPrices[] = $minPrice;
            $maxPrices[] = $maxPrice;
        }

        $ust = gibUst($this->articleData->kSteuerklasse);

        // Die jeweiligen Min- und Maxpreise sind die Summen aus allen Konfig-Gruppen
        $this->minNettoPrice += berechneNetto(array_sum($minPrices), $ust, 4);
        $this->maxNettoPrice += berechneNetto(array_sum($maxPrices), $ust, 4);
    }

    /**
     * @param float $discount
     * @return void
     */
    public function setDiscount(float $discount)
    {
        $discount = $discount / 100;

        if ($discount !== $this->discount) {
            $this->minNettoPrice = $this->minNettoPrice / (1 - $this->discount);
            $this->maxNettoPrice = $this->maxNettoPrice / (1 - $this->discount);

            $this->discount = $discount;

            $ust = gibUst($this->articleData->kSteuerklasse);

            $this->minNettoPrice  = $this->minNettoPrice * (1 - $this->discount);
            $this->maxNettoPrice  = $this->maxNettoPrice * (1 - $this->discount);
            $this->minBruttoPrice = berechneBrutto($this->minNettoPrice, $ust);
            $this->maxBruttoPrice = berechneBrutto($this->maxNettoPrice, $ust);
        }
    }

    /**
     * return
     *      true - if min price is lower than max price
     *      else - otherwise
     * @return bool
     */
    public function isRange()
    {
        return round($this->minNettoPrice, 2) < round($this->maxNettoPrice, 2);
    }

    /**
     * get range width in percent
     * @return float|int
     */
    public function rangeWidth()
    {
        return 100 / $this->minNettoPrice * $this->maxNettoPrice - 100;
    }

    /**
     * get localized min - max strings
     * @param int|null $netto
     * @return string|string[]
     */
    public function getLocalized(int $netto = null)
    {
        if ($netto !== null) {
            return $netto === 0
                ? $this->getMinLocalized(0) . ' - ' . $this->getMaxLocalized(0)
                : $this->getMinLocalized(1) . ' - ' . $this->getMaxLocalized(1);
        }

        return [
            $this->getMinLocalized(0) . ' - ' . $this->getMaxLocalized(0),
            $this->getMinLocalized(1) . ' - ' . $this->getMaxLocalized(1)
        ];
    }

    /**
     * get localized min strings
     * @param int|null $netto
     * @return string|string[]
     */
    public function getMinLocalized(int $netto = null)
    {
        $currency = Session::Currency();

        if ($netto !== null) {
            return $netto === 0
                ? gibPreisStringLocalized($this->minBruttoPrice, $currency)
                : gibPreisStringLocalized($this->minNettoPrice, $currency);
        }

        return [
            gibPreisStringLocalized($this->minBruttoPrice, $currency),
            gibPreisStringLocalized($this->minNettoPrice, $currency),
        ];
    }

    /**
     * get localized max strings
     * @param int|null $netto
     * @return string|string[]
     */
    public function getMaxLocalized(int $netto = null)
    {
        $currency = Session::Currency();

        if ($netto !== null) {
            return $netto === 0
                ? gibPreisStringLocalized($this->maxBruttoPrice, $currency)
                : gibPreisStringLocalized($this->maxNettoPrice, $currency);
        }

        return [
            gibPreisStringLocalized($this->maxBruttoPrice, $currency),
            gibPreisStringLocalized($this->maxNettoPrice, $currency),
        ];
    }
}
