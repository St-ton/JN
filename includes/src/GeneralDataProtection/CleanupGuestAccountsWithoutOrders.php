<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Deleted guest accounts with no open orders
 *
 * names of the tables, we manipulate:
 *
 * `tkunde`
 */
class CleanupGuestAccountsWithoutOrders extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute()
    {
        $this->cleanup_tkunde();
    }

    /**
     * delete not registered customers (relicts)
     */
    private function cleanup_tkunde()
    {
        $guestAccounts = \Shop::Container()->getDB()->queryPrepared(
            "SELECT kKunde
                FROM tkunde
                WHERE nRegistriert = 0
                  AND cAbgeholt ='Y'
                LIMIT :pLimit",
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        foreach ($guestAccounts as $guestAccount) {
            (new \Kunde((int)$guestAccount->kKunde))->deleteAccount(Journal::ISSUER_TYPE_APPLICATION, 0, true);
        }
    }
}
