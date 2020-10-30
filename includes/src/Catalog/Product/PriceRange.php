<?php

namespace JTL\Catalog\Product;

use JTL\DB\ReturnType;
use JTL\Extensions\Config\Configurator;
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
     * @var stdClass
     */
    private $productData;

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
     *
     * @param int $productID
     * @param int $customerGroupID
     * @param int $customerID
     */
    public function __construct(int $productID, int $customerGroupID = 0, int $customerID = 0)
    {
        if ($customerGroupID === 0) {
            $customerGroupID = Frontend::getCustomerGroup()->getID();
        }

        if ($customerID === 0) {
            $customerID = Frontend::getCustomer()->kKunde ?? 0;
        }

        $this->customerGroupID            = $customerGroupID;
        $this->customerID                 = $customerID;
        $this->discount                   = 0;
        $this->productData                = Shop::Container()->getDB()->select(
            'tartikel',
            'kArtikel',
            $productID,
            null,
            null,
            null,
            null,
            false,
            'kArtikel, kSteuerklasse, fLagerbestand, fStandardpreisNetto fNettoPreis'
        );
        $this->productData->kArtikel      = (int)$this->productData->kArtikel;
        $this->productData->kSteuerklasse = (int)$this->productData->kSteuerklasse;

        $this->loadPriceRange();
    }

    /**
     * load price range from database
     */
    private function loadPriceRange(): void
    {
        $priceRange = Shop::Container()->getDB()->queryPrepared(
            'SELECT fVKNettoMin, fVKNettoMax 
                FROM tpricerange
                WHERE kArtikel = :articleID
                    AND (
                        (kKundengruppe = 0 AND kKunde = :customerID)
                        OR
                        (kKundengruppe = :customerGroup
                            AND COALESCE(nLagerAnzahlMax, :stock) <= :stock
                            AND CURDATE() BETWEEN COALESCE(dStart, CURDATE()) AND COALESCE(dEnde, CURDATE())
                        )
                    )
                ORDER BY nRangeType ASC LIMIT 1',
            [
                'articleID'     => $this->productData->kArtikel,
                'customerGroup' => $this->customerGroupID,
                'customerID'    => $this->customerID,
                'stock'         => $this->productData->fLagerbestand,
            ],
            ReturnType::SINGLE_OBJECT
        );

        if ($priceRange) {
            $this->minNettoPrice = (float)$priceRange->fVKNettoMin;
            $this->maxNettoPrice = (float)$priceRange->fVKNettoMax;
        } else {
            $this->minNettoPrice = $this->productData->fNettoPreis;
            $this->maxNettoPrice = $this->productData->fNettoPreis;
        }

        if (Configurator::hasKonfig($this->productData->kArtikel)) {
            $this->loadConfiguratorRange();
        }

        $ust = Tax::getSalesTax($this->productData->kSteuerklasse);

        $this->minBruttoPrice = Tax::getGross($this->minNettoPrice, $ust);
        $this->maxBruttoPrice = Tax::getGross($this->maxNettoPrice, $ust);
    }

    public function loadConfiguratorRange(): void
    {
        $configItems = Shop::Container()->getDB()->queryPrepared(
            'SELECT tartikel.kArtikel,
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
                    IF(tkonfigitem.bPreis = 0, tkonfigitempreis.kSteuerklasse, tartikel.kSteuerklasse)',
            [
                'articleID'     => $this->productData->kArtikel,
                'customerGroup' => $this->customerGroupID,
            ],
            ReturnType::ARRAY_OF_OBJECTS
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
                $priceRange = new PriceRange((int)$configItem->kKindArtikel, $this->customerGroupID, $this->customerID);
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
            $this->minNettoPrice /= (1 - $this->discount);
            $this->maxNettoPrice /= (1 - $this->discount);

            $this->discount = $discount;

            $ust = Tax::getSalesTax($this->productData->kSteuerklasse);

            $this->minNettoPrice *= (1 - $this->discount);
            $this->maxNettoPrice *= (1 - $this->discount);
            $this->minBruttoPrice = Tax::getGross($this->minNettoPrice, $ust);
            $this->maxBruttoPrice = Tax::getGross($this->maxNettoPrice, $ust);
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
    public function rangeWidth()
    {
        return (int)$this->minNettoPrice !== 0
            ? 100 / $this->minNettoPrice * $this->maxNettoPrice - 100
            : 0;
    }

    /**
     * get localized min - max strings
     *
     * @param int|null $netto
     * @return string|string[]
     * @deprecated since 5.0.0
     */
    public function getLocalized(int $netto = null)
    {
        $rangePrices = $this->getLocalizedArray($netto);

        if ($netto !== null) {
            return $rangePrices[0] . ' - '. $rangePrices[1];
        }

        return [
            $rangePrices[0][0] . ' - '. $rangePrices[0][1],
            $rangePrices[1][0] . ' - '. $rangePrices[1][1],
        ];
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
    public function getMinLocalized(int $netto = null)
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
    public function getMaxLocalized(int $netto = null)
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
}
