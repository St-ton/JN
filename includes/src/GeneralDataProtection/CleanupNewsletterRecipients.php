<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

class CleanupNewsletterRecipients implements MethodInterface
{
    public function __construct()
    {
        //
    }

    /**
     * Delete newsletter-registrations with no opt-in within given interval
     * (interval former "interval_clear_logs" = 90 days)
     *
     * @param int
     */
    public function execute(int $iInterval)
    {
        \Shop::Container()->getDB()->queryPrepared('DELETE e, h
            FROM `tnewsletterempfaenger` e
                JOIN `tnewsletterempfaengerhistory` h ON h.`cOptCode` = e.`cOptCode` AND h.`cEmail` = e.`cEmail`
            WHERE e.`nAktiv`=0 AND h.`cAktion` = "Eingetragen"
                AND h.`dOptCode` = "0000-00-00"
                AND h.dEingetragen <= (now() - INTERVAL ' . $iInterval . ' DAY)'
            , []
            , \DB\ReturnType::SINGLE_OBJECT
        );
    }

}
