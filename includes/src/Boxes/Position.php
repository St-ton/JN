<?php declare(strict_types=1);
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
    public const LEFT = 'left';

    public const RIGHT = 'right';

    public const BOTTOM = 'bottom';

    public const TOP = 'top';
}
