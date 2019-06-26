<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Smarty;

use MyCLabs\Enum\Enum;

/**
 * Class ContextType
 * @package JTL\Smarty
 */
class ContextType extends Enum
{
    public const FRONTEND = 'frontend';

    public const BACKEND = 'backend';

    public const MAIL = 'mail';

    public const NEWSLETTER = 'newsletter';

    public const EXPORT = 'export';

    public const CLI = 'cli';
}
