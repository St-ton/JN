<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GdprAnonymizing;

class CleanupDeletedGuestAccounts implements MethodInterface
{
    public function __construct()
    {
        //
    }

    /**
     * Deleted guest accounts with no open orders
     */
    public function execute()
    {
        \Shop::Container()->getDB()->queryPrepared('DELETE k
            FROM `tkunde` k
                JOIN `tbestellung` b ON b.`kKunde` = k.`kKunde`
            WHERE
                b.`cStatus` IN (4, -1)
                AND k.`nRegistriert` = 0
                AND b.`cAbgeholt` = "Y"'
            , []
            , \DB\ReturnType::AFFECTED_ROWS
        );
    }

}
