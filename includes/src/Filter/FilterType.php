<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

use MyCLabs\Enum\Enum;

/**
 * Class ComponentPropertyType
 *
 * @package bs4
 * @method FilterType OR()
 * @method FilterType AND()
 */
class FilterType extends Enum
{
    /**
     * filter can increase product amount
     */
    const OR = 0;

    /**
     * filter will decrease product amount
     */
    const AND = 1;
}
