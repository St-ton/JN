<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Filter;

use MyCLabs\Enum\Enum;

/**
 * Class Type
 *
 * @package Filter
 * @method static Type OR()
 * @method static Type AND()
 */
class Type extends Enum
{
    /**
     * filter can increase product amount
     */
    public const OR = 0;

    /**
     * filter will decrease product amount
     */
    public const AND = 1;
}
