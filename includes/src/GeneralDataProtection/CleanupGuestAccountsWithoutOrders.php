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
        $this->workLimit = 50;        // override main value from Method class (can be configured here)      --TODO-- reset to 100 !

        $this->cleanupCustomers();
    }

    /**
     * delete not registered customers (relicts)
     *
     * @return void
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
        foreach ($guestAccounts as $guestAccount) {
            $customer = new Customer((int)$guestAccount->kKunde);
            $delRes   = $customer->deleteAccount(Journal::ISSUER_TYPE_APPLICATION, 0);

            if ($delRes === Customer::CUSTOMER_DELETE_DEACT ||
                $delRes === Customer::CUSTOMER_DELETE_DONE) {
                $oLogger->debug((int)$guestAccount->kKunde.' - remove-call: DELETE_DEACT, DELETE_DONE .. '.print_r($delRes, true));   // --DEBUG--
                $this->workSum++;
            // --DEBUG-- the following `else` can be removed completely
            } else {
                $oLogger->debug((int)$guestAccount->kKunde.' - remove-call: DELETE_NO .. '.print_r($delRes, true));   // --DEBUG--
            }
        }
        $oLogger->debug('workSum: '.$this->workSum);   // --DEBUG--

        if ($this->workSum === 0) {
            // $finished = ($this->workSum === 0);                      // runs in "workLimit" steps until nothing is to do anymore
            $finished              = true;
            $this->taskRepetitions = 0;
        } else {
            $finished = false;
            $this->taskRepetitions--;
        }
        $oLogger->debug('is finished: '.($finished ? 'true' : 'false').'    (repetiions left: '.$this->taskRepetitions.')');   // --DEBUG--
        $this->isFinished = ($finished || $this->taskRepetitions === 0);
    }
}
