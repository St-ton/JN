<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;


use MyCLabs\Enum\Enum;

/**
 * Class Type
 * @package Cron
 */
class Type extends Enum
{
    const EXPORT = 'exportformat';

    const STATUSMAIL = 'statusemail';

    const TS_RATING = 'tskundenbewertung';

    const CLEAR_CACHE = 'clearcache';

    const NEWSLETTER = 'newsletter';

    const PLUGIN = 'plugin';

}
