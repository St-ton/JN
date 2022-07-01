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
     */
    public function execute(): void
    {
        $this->workLimit = 100; // override main value from Method class (can be configured here)

        $this->cleanupCustomers();
    }

    /**
     * delete not registered customers (relicts)
     */
    private function cleanupCustomers(): void
    {
        // --DEBUG-- -------------------------------------------------------------
        require_once('/www/shop5_02/includes/vendor/apache/log4php/src/main/php/Logger.php');
        \Logger::configure('/www/shop5_02/_logging_conf.xml');
        $oLogger = \Logger::getLogger('default');
        // --DEBUG-- -------------------------------------------------------------

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
        $this->workSum    = count($guestAccounts);
        $this->isFinished = (count($guestAccounts) === 0);
        foreach ($guestAccounts as $guestAccount) {
            // (new Customer((int)$guestAccount->kKunde))->deleteAccount(Journal::ISSUER_TYPE_APPLICATION, 0);

            $customer = new Customer((int)$guestAccount->kKunde);
            $r = $customer->deleteAccount(Journal::ISSUER_TYPE_APPLICATION, 0);

            // --TO-CHECK-- the following ist wrong - it's only 'n idea
            switch ($customer->deleteAccount(Journal::ISSUER_TYPE_APPLICATION, 0)) {
                case Customer::CUSTOMER_DELETE_DEACT :
                case Customer::CUSTOMER_DELETE_DONE :
                    break;
                case Customer::CUSTOMER_DELETE_NO :
                    break;
                default :
                    break;
            }
            $oLogger->debug('remove-call: '.print_r($r, true));   // --DEBUG--
        }
    }
}
