<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Boxes;

use MyCLabs\Enum\Enum;

/**
 * Class ComponentPropertyType
 *
 * @package Boxes
 * @method BoxPosition LEFT()
 * @method BoxPosition RIGHT()
 * @method BoxPosition BOTTOM()
 * @method BoxPosition TOP()
 */
class BoxPosition extends Enum
{
    const LEFT = 'left';

    const RIGHT = 'right';

    const BOTTOM = 'bottom';

    const TOP = 'top';
}
