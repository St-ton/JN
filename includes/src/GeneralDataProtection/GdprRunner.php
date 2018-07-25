<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GdprAnonymizing;

/**
 * class GdprController
 * ("Global Data Protection Rules", german: "DSGVO")
 */
class GdprRunner
{
    private $szLastRun = null;
    private $bTimerReset = false; // only after 7 days ...--TODO--


    public function __construct()
    {
        $oResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT `dLastRun` FROM `tanonymizer`', []
            , \DB\ReturnType::SINGLE_OBJECT
        );
        $this->szLastRun = (isset($oResult->dLastRun) && false !== $oResult->dLastRun)
            ? (new \DateTime($oResult->dLastRun))->format('Y-m-d H:i:s')
            : null;
        if (null === $this->szLastRun) {
            $this->szLastRun = (new \DateTime())->format('Y-m-d H:i:s');
        }
        $oTimeDistance = (new \DateTime())->diff(new \DateTime($this->szLastRun));
        // reset the "run-timer" (and release the "run-lock" this way)
        if (\GdprAnonymizing\Intervals::TIMER_RESET <= $oTimeDistance->d) {
            $this->bTimerReset = true;
        }
    }

    public function execute()
    {
        // all methods are private ! --TODO--
        if (true !== $this->bTimerReset) {
            return false;
        }

        $this->oLogger->debug('RUNNING ... each 7th day '); // --TRY-OUT--

        // anonymize IPs each 7 days (except these, which was anonymized immediately)

        // --TO-CHECK--
        // for now:
        // anon all IPs, which has to be anonymized the end of the next year after there creation
        /*
         *$oMethod = (new \GdprAnonymizing\AnonymizeIps())->execute();
         */
        // --TODO-- tfsession ? truncat

        // --TO-CHECK--
        // anon `tbewertung`, `tzahlungseingang`, `tnewskommentar`
        // (no intervals, update only)
        /*
         *$oMethod = (new \GdprAnonymizing\AnonymizeDeletedCustomer())->execute();
         */

        // delete guest-accounts with no open orders (no interval, each call)
        // (no intervals, removing only)
        /*
         *$oMethod = (new \GdprAnonymizing\CleanupDeletedGuestAccounts())->execute();
         */

        // Delete customer relicts in logs and subtables and delete shipping and billing-addresses of deleted customers
        // (no intervals, removing only)
        /*
         *$oMethod = (new \GdprAnonymizing\CleanupCustomerRelicts())->execute();
         */

        // Delete newsletter-registrations with no opt-in within given interval
        // (INTERVAL! removing by interval)
        /*
         *$oMethod = new \GdprAnonymizing\CleanupNewsletterRecipients();
         *$oMethod->execute(\GdprAnonymizing\Intervals::TC_DAYS_30);
         */

        // Delete old logs containing personal data.
        // (Customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu))
        // (INTERVALS! removing by interval)
        /*
         *$oMethod = new \GdprAnonymizing\CleanupLogs();
         *$oMethod->execute(\GdprAnonymizing\Intervals::TC_DAYS_90);
         */

        // Remove guest accounts fetched by JTL-Wawi and older than x days
        // (INTERVALS, removing by interval)
        /*
         *$oMethod = new \GdprAnonymizing\CleanupOldGuestAccounts();
         *$oMethod->execute(\GdprAnonymizing\Intervals::TC_DAYS_365);
         */

    }


    public function __destruct()
    {
        if (true === $this->bTimerReset) {
            $this->szLastRun = \Shop::Container()->getDB()->queryPrepared(
                'INSERT INTO `tanonymizer` VALUES(1, now()) ON DUPLICATE KEY UPDATE `dLastRun`=now() ', []
                , \DB\ReturnType::SINGLE_OBJECT
            );
        }
    }

}
