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
     * @inheritDoc
     *
     * @var integer
     */
    protected $workLimit = 100;

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
        $guestAccounts      = $this->db->getObjects(
            "SELECT kKunde
                FROM tkunde
                WHERE
                    nRegistriert = 0
                    AND cAbgeholt = 'Y'
                    AND cKundenNr != :anonym
                    AND cVorname != :anonym
                    AND cNachname != :anonym
                LIMIT :worklimit",
            [
                'anonym'    => Customer::CUSTOMER_ANONYM,
                'worklimit' => $this->workLimit
            ]
        );
        $this->isFinished = (count($guestAccounts) === 0);
        foreach ($guestAccounts as $guestAccount) {
            (new Customer((int)$guestAccount->kKunde))->deleteAccount(Journal::ISSUER_TYPE_APPLICATION, 0);
        }
    }
}
