<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron;

use MyCLabs\Enum\Enum;

/**
 * Class Type
 * @package JTL\Cron
 */
class Type extends Enum
{
    public const EXPORT = 'exportformat';

    public const STATUSMAIL = 'statusemail';

    public const TS_RATING = 'tskundenbewertung';

    public const CLEAR_CACHE = 'clearcache';

    public const NEWSLETTER = 'newsletter';

    public const PLUGIN = 'plugin';

    public const DATAPROTECTION = 'dataprotection';

    public const IMAGECACHE = 'imagecache';

    public const STORE = 'store';
}
