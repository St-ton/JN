<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GdprAnonymizing;

class CleanupOldGuestAccounts implements MethodInterface
{
    public function __construct()
    {
        //
    }

    /**
     * Remove guest accounts fetched by JTL-Wawi and older than x days
     * (interval former "interval_delete_guest_accounts" = 365 days)
     *
     * @param int
     */
    public function execute(int $nIntervalDays)
    {
        \Shop::Container()->getDB()->queryPrepared('DELETE FROM
                `tkunde`
            WHERE
                `nRegistriert` = 0
                AND `cAbgeholt` = "Y"
                AND dErstellt <= NOW() - INTERVAL ' . $nIntervalDays . ' DAY'
            , []
            , \DB\ReturnType::SINGLE_OBJECT
        );
    }

}
