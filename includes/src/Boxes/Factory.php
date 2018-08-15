<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;


use Boxes\Items\{BestsellingProducts,
    BoxInterface,
    Cart,
    CompareList,
    Container,
    DirectPurchase,
    FilterAttribute,
    FilterCategory,
    FilterItem,
    FilterManufacturer,
    FilterPricerange,
    FilterRating,
    FilterSearch,
    FilterTag,
    GlobalAttributes,
    LinkGroup,
    Login,
    Manufacturer,
    NewProducts,
    NewsCategories,
    NewsCurrentMonth,
    Plain,
    Plugin,
    Poll,
    PriceRadar,
    ProductCategories,
    RecentlyViewedProducts,
    SearchCloud,
    SpecialOffers,
    TagCloud,
    TopOffers,
    TopRatedProducts,
    TrustedShopsReviews,
    TrustedShopsSeal,
    UpcomingProducts,
    Wishlist
};

/**
 * Class Factory
 *
 * @package Boxes
 */
class Factory implements FactoryInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * Factory constructor.
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
            case \BOX_BESTSELLER:
                return new BestsellingProducts($this->config);
            case \BOX_CONTAINER:
                return new Container($this->config);
            case \BOX_IN_KUERZE_VERFUEGBAR:
                return new UpcomingProducts($this->config);
            case \BOX_ZULETZT_ANGESEHEN:
                return new RecentlyViewedProducts($this->config);
            case \BOX_NEUE_IM_SORTIMENT:
                return new NewProducts($this->config);
            case \BOX_TOP_ANGEBOT:
                return new TopOffers($this->config);
            case \BOX_SONDERANGEBOT:
                return new SpecialOffers($this->config);
            case \BOX_LOGIN:
                return new Login($this->config);
            case \BOX_GLOBALE_MERKMALE:
                return new GlobalAttributes($this->config);
            case \BOX_KATEGORIEN:
                return new ProductCategories($this->config);
            case \BOX_NEWS_KATEGORIEN:
                return new NewsCategories($this->config);
            case \BOX_NEWS_AKTUELLER_MONAT:
                return new NewsCurrentMonth($this->config);
            case \BOX_TAGWOLKE:
                return new TagCloud($this->config);
            case \BOX_WUNSCHLISTE:
                return new Wishlist($this->config);
            case \BOX_WARENKORB:
                return new Cart($this->config);
            case \BOX_SCHNELLKAUF:
                return new DirectPurchase($this->config);
            case \BOX_VERGLEICHSLISTE:
                return new CompareList($this->config);
            case \BOX_EIGENE_BOX_MIT_RAHMEN:
            case \BOX_EIGENE_BOX_OHNE_RAHMEN:
                return new Plain($this->config);
            case \BOX_LINKGRUPPE:
                return new LinkGroup($this->config);
            case \BOX_UMFRAGE:
                return new Poll($this->config);
            case \BOX_PREISRADAR:
                return new PriceRadar($this->config);
            case \BOX_HERSTELLER:
                return new Manufacturer($this->config);
            case \BOX_FILTER_MERKMALE:
                return new FilterAttribute($this->config);
            case \BOX_FILTER_KATEGORIE:
                return new FilterCategory($this->config);
            case \BOX_FILTER_HERSTELLER:
                return new FilterManufacturer($this->config);
            case \BOX_FILTER_TAG:
                return new FilterTag($this->config);
            case \BOX_FILTER_PREISSPANNE:
                return new FilterPricerange($this->config);
            case \BOX_FILTER_BEWERTUNG:
                return new FilterRating($this->config);
            case \BOX_FILTER_SUCHE:
                return new FilterSearch($this->config);
            case \BOX_FILTER_SUCHSPECIAL:
                return new FilterItem($this->config);
            case \BOX_TRUSTEDSHOPS_GUETESIEGEL:
                return new TrustedShopsSeal($this->config);
            case \BOX_TRUSTEDSHOPS_KUNDENBEWERTUNGEN:
                return new TrustedShopsReviews($this->config);
            case \BOX_TOP_BEWERTET:
                return new TopRatedProducts($this->config);
            case \BOX_SUCHWOLKE:
                return new SearchCloud($this->config);
            default:
                return $isPlugin ? new Plugin($this->config) : new Items\BoxDefault($this->config);
        }
    }
}
