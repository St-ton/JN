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
            $customerID = Session::Customer()->kKunde;
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
                    AND kKundengruppe = :customerGroup
                    AND (
                        :customerID IS NULL OR kKunde = :customerID
                        OR nLagerAnzahlMax IS NULL OR (nLagerAnzahlMax <= :stock AND dStart <= CURDATE())
                        OR dEnde IS NULL OR (CURDATE() BETWEEN dStart AND dEnde)
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

        $ust = gibUst($this->articleData->kSteuerklasse);

        $this->minBruttoPrice = berechneBrutto($this->minNettoPrice, $ust);
        $this->maxBruttoPrice = berechneBrutto($this->maxNettoPrice, $ust);
    }

    /**
     * @param float $discount
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
        return $this->minNettoPrice < $this->maxNettoPrice;
    }

    /**
     * get array (brutto, netto) of localized strings
     * @param int|null $netto
     * @return string|string[]
     */
    public function getLocalized(int $netto = null)
    {
        $currency = Session::Currency();

        if ($netto !== null) {
            switch ($netto) {
                case 0:
                    return gibPreisStringLocalized($this->minBruttoPrice, $currency)
                        . ' - '
                        . gibPreisStringLocalized($this->maxBruttoPrice, $currency);
                case 1:
                    return gibPreisStringLocalized($this->minNettoPrice, $currency)
                        . ' - '
                        . gibPreisStringLocalized($this->maxNettoPrice, $currency);
            }
        }

        return [
            gibPreisStringLocalized($this->minBruttoPrice, $currency) . ' - ' . gibPreisStringLocalized($this->maxBruttoPrice, $currency),
            gibPreisStringLocalized($this->minNettoPrice, $currency) . ' - ' . gibPreisStringLocalized($this->maxNettoPrice, $currency),
        ];
    }

    public function getMinLocalized(int $netto = null)
    {
        $currency = Session::Currency();

        if ($netto !== null) {
            switch ($netto) {
                case 0:
                    return gibPreisStringLocalized($this->minBruttoPrice, $currency);
                case 1:
                    return gibPreisStringLocalized($this->minNettoPrice, $currency);
            }
        }

        return [
            gibPreisStringLocalized($this->minBruttoPrice, $currency),
            gibPreisStringLocalized($this->minNettoPrice, $currency),
        ];
    }

    public function getMaxLocalized(int $netto = null)
    {
        $currency = Session::Currency();

        if ($netto !== null) {
            switch ($netto) {
                case 0:
                    return gibPreisStringLocalized($this->maxBruttoPrice, $currency);
                case 1:
                    return gibPreisStringLocalized($this->maxNettoPrice, $currency);
            }
        }

        return [
            gibPreisStringLocalized($this->maxBruttoPrice, $currency),
            gibPreisStringLocalized($this->maxNettoPrice, $currency),
        ];
    }
}
