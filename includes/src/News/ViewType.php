<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


use MyCLabs\Enum\Enum;

/**
 * Class ViewType
 * @package News
 */
class ViewType extends Enum
{
    const NEWS_DISABLED = -1;

    const NEWS_UNKNOWN = 0;

    const NEWS_DETAIL = 1;

    const NEWS_CATEGORY = 2;

    const NEWS_MONTH_OVERVIEW = 3;

    const NEWS_OVERVIEW = 4;
}
