<?php declare(strict_types=1);
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
 * @method static InputType SELECT()
 * @method static InputType CHECKBOX()
 * @method static InputType BUTTON()
 */
class InputType extends Enum
{
    /**
     * filter type selectbox
     */
    public const SELECT = 1;

    /**
     * filter type checkbox
     */
    public const CHECKBOX = 2;

    /**
     * filter type button
     */
    public const BUTTON = 3;
}
