<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Interface UstIDviesInterface
 */
interface UstIDviesInterface
{
    public const NO_ID_GIVEN             = 1;   // error: no $szUstID was given
    public const PATTERNLENGTH_NOT_FOUND = 110; // error: no length was matching
    public const COUNTRY_NOT_FOUND       = 130; // error: no pattern for such a country
    public const PATTERN_NOT_MATCH       = 120; // error: id did not match any pattern of this country

    /**
     * @param string $szUstID
     */
    public function doCheckID($szUstID);
}
