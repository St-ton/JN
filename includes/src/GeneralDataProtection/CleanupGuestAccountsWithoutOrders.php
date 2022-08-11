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
     * "can be unfinished 5 times"
     *
     * @var int
     */
    public $taskRepetitions = 5;


    /**
     * @inheritDoc
     * @return void
     */
    public function execute(): void
    {
        $this->workLimit = 100;        // override main value from Method class (can be configured here)

        $this->cleanupCustomers();
    }

    /**
     * delete not registered customers (relicts)
     *
     * @return void
     */
    private function cleanupCustomers(): void
    {
        $guestAccounts    = $this->db->getObjects(
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
        foreach ($guestAccounts as $guestAccount) {
            $customer = new Customer((int)$guestAccount->kKunde);
            $delRes   = $customer->deleteAccount(Journal::ISSUER_TYPE_APPLICATION, 0);
            if ($delRes === Customer::CUSTOMER_DELETE_DEACT ||
                $delRes === Customer::CUSTOMER_DELETE_DONE) {
                $this->workSum++;
            }
        }
        if ($this->workSum === 0) {
            $finished              = true;
            $this->taskRepetitions = 0;
        } else {
            $finished = false;
            $this->taskRepetitions--;
        }
        $this->isFinished = ($finished || $this->taskRepetitions === 0);
    }
}
