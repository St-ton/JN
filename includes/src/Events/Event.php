<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Events;

use MyCLabs\Enum\Enum;

/**
 * Class Event
 * @package Events
 */
class Event extends Enum
{
    public const RUN = 'shop.run';

    public const MAP_CRONJOB_TYPE = 'map.cronjob.type';

    public const GET_AVAILABLE_CRONJOBS = 'get.available.cronjobs';
}
