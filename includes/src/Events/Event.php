<?php

namespace JTL\Events;

use MyCLabs\Enum\Enum;

/**
 * Class Event
 * @package JTL\Events
 */
class Event extends Enum
{
    public const RUN = 'shop.run';

    public const MAP_CRONJOB_TYPE = 'map.cronjob.type';

    public const GET_AVAILABLE_CRONJOBS = 'get.available.cronjobs';
}
