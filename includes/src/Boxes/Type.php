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
 * @method Type DEFAULT()
 * @method Type PLUGIN()
 * @method Type TEXT()
 * @method Type LINK()
 * @method Type CATBOX()
 */
class Type extends Enum
{
    public const DEFAULT = 'default';

    public const PLUGIN = 'plugin';

    public const TEXT = 'text';

    public const LINK = 'link';

    public const CATBOX = 'catbox';

    public const TPL = 'tpl';

    public const CONTAINER = 'container';
}
