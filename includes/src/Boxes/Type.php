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
 * @method Type DEFAULT()
 * @method Type PLUGIN()
 * @method Type TEXT()
 * @method Type LINK()
 * @method Type CATBOX()
 */
class Type extends Enum
{
    const DEFAULT = 'default';

    const PLUGIN = 'plugin';

    const TEXT = 'text';

    const LINK = 'link';

    const CATBOX = 'catbox';

    const TPL = 'tpl';

    const CONTAINER = 'container';
}
