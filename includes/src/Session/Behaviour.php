<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Session;

use MyCLabs\Enum\Enum;

/**
 * Class Behaviour
 * @package Session
 */
class Behaviour extends Enum
{
    /**
     * handle bot like normal visitor
     */
    public const DEFAULT = 0;

    /**
     * use single session ID for all bot visits
     */
    public const COMBINE = 1;

    /**
     * save combined bot session to cache
     */
    public const CACHE = 2;

    /**
     * never save bot sessions
     */
    public const NO_SAVE = 3;
}
