<?php declare(strict_types=1);

namespace JTL\Reset;

use MyCLabs\Enum\Enum;

/**
 * Class ResetContentType
 * @package JTL\Reset
 * @extends Enum<string>
 * @method static self PRODUCTS()
 * @method static self TAXES()
 * @method static self REVISIONS()
 * @method static self NEWS()
 * @method static self BESTSELLER()
 * @method static self STATS_VISITOR()
 * @method static self STATS_PRICES()
 * @method static self MESSAGES_AVAILABILITY()
 * @method static self SEARCH_REQUESTS()
 * @method static self RATINGS()
 * @method static self WISHLIST()
 * @method static self COMPARELIST()
 * @method static self CUSTOMERS()
 * @method static self ORDERS()
 * @method static self COUPONS()
 * @method static self SETTINGS()
 */
class ResetContentType extends Enum
{
    public const PRODUCTS              = 'artikel';
    public const TAXES                 = 'steuern';
    public const REVISIONS             = 'revisions';
    public const NEWS                  = 'news';
    public const BESTSELLER            = 'bestseller';
    public const STATS_VISITOR         = 'besucherstatistiken';
    public const STATS_PRICES          = 'preisverlaeufe';
    public const MESSAGES_AVAILABILITY = 'verfuegbarkeitsbenachrichtigungen';
    public const SEARCH_REQUESTS       = 'suchanfragen';
    public const RATINGS               = 'bewertungen';
    public const WISHLIST              = 'wishlist';
    public const COMPARELIST           = 'comparelist';
    public const CUSTOMERS             = 'shopkunden';
    public const ORDERS                = 'bestellungen';
    public const COUPONS               = 'kupons';
    public const SETTINGS              = 'shopeinstellungen';

    /**
     * ResetMethod constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        parent::__construct($value);
    }
}
