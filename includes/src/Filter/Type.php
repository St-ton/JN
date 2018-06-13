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
 * @package Filter
 * @method Type OR()
 * @method Type AND()
 */
class Type extends Enum
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
