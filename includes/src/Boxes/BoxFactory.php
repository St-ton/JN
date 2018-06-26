<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

/**
 * Class BoxFactory
 *
 * @package Boxes
 */
class BoxFactory implements BoxFactoryInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * BoxFactory constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getBoxByBaseType(int $baseType, bool $isPlugin): BoxInterface
    {
        switch ($baseType) {
            case BOX_BESTSELLER:
                return new BoxBestsellingProducts($this->config);
            case BOX_CONTAINER:
                return new BoxContainer($this->config);
            case BOX_IN_KUERZE_VERFUEGBAR:
                return new BoxUpcomingProducts($this->config);
            case BOX_ZULETZT_ANGESEHEN:
                return new BoxRecentlyViewedProducts($this->config);
            case BOX_NEUE_IM_SORTIMENT:
                return new BoxNewProducts($this->config);
            case BOX_TOP_ANGEBOT:
                return new BoxTopOffers($this->config);
            case BOX_SONDERANGEBOT:
                return new BoxSpecialOffers($this->config);
            case BOX_LOGIN:
                return new BoxLogin($this->config);
            case BOX_GLOBALE_MERKMALE:
                return new BoxGlobalAttributes($this->config);
            case BOX_KATEGORIEN:
                return new BoxProductCategories($this->config);
            case BOX_NEWS_KATEGORIEN:
                return new BoxNewsCategories($this->config);
            case BOX_NEWS_AKTUELLER_MONAT:
                return new BoxNewsCurrentMonth($this->config);
            case BOX_TAGWOLKE:
                return new BoxTagCloud($this->config);
            case BOX_WUNSCHLISTE:
                return new BoxWishlist($this->config);
            case BOX_WARENKORB:
                return new BoxCart($this->config);
            case BOX_SCHNELLKAUF:
                return new BoxDirectPurchase($this->config);
            case BOX_VERGLEICHSLISTE:
                return new BoxCompareList($this->config);
            case BOX_EIGENE_BOX_MIT_RAHMEN:
            case BOX_EIGENE_BOX_OHNE_RAHMEN:
                return new BoxPlain($this->config);
            case BOX_LINKGRUPPE:
                return new BoxLinkGroup($this->config);
            case BOX_UMFRAGE:
                return new BoxPoll($this->config);
            case BOX_PREISRADAR:
                return new BoxPriceRadar($this->config);
            case BOX_HERSTELLER:
                return new BoxManufacturer($this->config);
            case BOX_FILTER_MERKMALE:
                return new BoxFilterAttribute($this->config);
            case BOX_FILTER_KATEGORIE:
                return new BoxFilterCategory($this->config);
            case BOX_FILTER_HERSTELLER:
                return new BoxFilterManufacturer($this->config);
            case BOX_FILTER_TAG:
                return new BoxFilterTag($this->config);
            case BOX_FILTER_PREISSPANNE:
                return new BoxFilterPricerange($this->config);
            case BOX_FILTER_BEWERTUNG:
                return new BoxFilterRating($this->config);
            case BOX_FILTER_SUCHE:
                return new BoxFilterSearch($this->config);
            case BOX_FILTER_SUCHSPECIAL:
                return new BoxFilterItem($this->config);
            case BOX_TRUSTEDSHOPS_GUETESIEGEL:
                return new BoxTrustedShopsSeal($this->config);
            case BOX_TRUSTEDSHOPS_KUNDENBEWERTUNGEN:
                return new BoxTrustedShopsReviews($this->config);
            default:
                return $isPlugin ? new BoxPlugin($this->config) : new BoxDefault($this->config);
        }
    }
}