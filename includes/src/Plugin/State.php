<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

use MyCLabs\Enum\Enum;

/**
 * Class State
 * @package Plugin
 */
class State extends Enum
{
    public const DISABLED = 1;

    public const ACTIVATED = 2;

    public const ERRONEOUS = 3;

    public const UPDATE_FAILED = 4;

    public const LICENSE_KEY_MISSING = 5;

    public const LICENSE_KEY_INVALID = 6;
}
