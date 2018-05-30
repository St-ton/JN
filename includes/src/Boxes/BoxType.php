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
 * @method BoxType DEFAULT()
 * @method BoxType PLUGIN()
 * @method BoxType TEXT()
 * @method BoxType LINK()
 * @method BoxType CATBOX()
 */
class BoxType extends Enum
{
    const DEFAULT = 'default';

    const PLUGIN = 'plugin';

    const TEXT = 'type';

    const LINK = 'link';

    const CATBOX = 'catbox';

    const TPL = 'tpl';

    const CONTAINER = 'container';
}
