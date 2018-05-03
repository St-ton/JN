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
 * @method InputType SELECT()
 * @method InputType CHECKBOX()
 * @method InputType BUTTON()
 */
class InputType extends Enum
{
    /**
     * filter type selectbox
     */
    const SELECT = 1;

    /**
     * filter type checkbox
     */
    const CHECKBOX = 2;

    /**
     * filter type button
     */
    const BUTTON = 3;
}
