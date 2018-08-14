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
 * @method Position LEFT()
 * @method Position RIGHT()
 * @method Position BOTTOM()
 * @method Position TOP()
 */
class Position extends Enum
{
    const LEFT = 'left';

    const RIGHT = 'right';

    const BOTTOM = 'bottom';

    const TOP = 'top';
}
