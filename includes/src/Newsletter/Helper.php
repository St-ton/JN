<?php declare(strict_types=1);

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

    /**
     * @param int $historyID
     * @param int $customerGroup
     * @return bool
     */
    public static function customerGroupHasHistory(int $historyID, int $customerGroup): bool
    {
        $history = Shop::Container()->getDB()->queryPrepared(
            "SELECT kNewsletterHistory, nAnzahl, cBetreff, cHTMLStatic, cKundengruppeKey,
            DATE_FORMAT(dStart, '%d.%m.%Y %H:%i') AS Datum
            FROM tnewsletterhistory
            WHERE kNewsletterHistory = :hid",
            ['hid' => $historyID],
            ReturnType::SINGLE_OBJECT
        );
        $groups  = Text::parseSSKint($history->cKundengruppeKey ?? '');

        return \in_array(0, $groups, true) || \in_array($customerGroup, $groups, true);
    }
}
