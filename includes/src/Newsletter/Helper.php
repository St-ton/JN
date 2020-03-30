<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Newsletter;

use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Shop;

/**
 * Class Helper
 * @package JTL\Newsletter
 */
class Helper
{
    /**
     * @param int $customerID
     * @return bool
     */
    public static function customerIsSubscriber(int $customerID): bool
    {
        $recipient = Shop::Container()->getDB()->select('tnewsletterempfaenger', 'kKunde', $customerID);

        return ($recipient->kKunde ?? 0) > 0;
    }
}
