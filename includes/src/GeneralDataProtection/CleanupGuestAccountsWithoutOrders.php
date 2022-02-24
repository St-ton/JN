<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\Customer\Customer;

/**
 * Class CleanupGuestAccountsWithoutOrders
 * @package JTL\GeneralDataProtection
 *
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
    public function execute(): void
    {
        $this->cleanupCustomers();
    }

    /**
     * delete not registered customers (relicts)
     */
    private function cleanupCustomers(): void
    {
        $guestAccounts = $this->db->getInts(
            "SELECT kKunde
                FROM tkunde
                WHERE
                    nRegistriert = 0
                    AND cAbgeholt = 'Y'
                    AND cKundenNr != :anon
                    AND cVorname != :anon
                    AND cNachname != :anon
                LIMIT :lmt",
            'kKunde',
            [
                'lmt'  => $this->workLimit,
                'anon' => Customer::CUSTOMER_ANONYM
            ]
        );
        foreach ($guestAccounts as $accountID) {
            (new Customer($accountID))->deleteAccount(Journal::ISSUER_TYPE_APPLICATION, 0);
        }
    }
}
