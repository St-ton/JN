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


    private $oLogger = null;

    public function __construct()
    {
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        \Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = \Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--

        $oResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT `dLastRun` FROM `tanonymizer`', []
            , \DB\ReturnType::SINGLE_OBJECT
        );
        $this->szLastRun = (isset($oResult->dLastRun) && false !== $oResult->dLastRun)
            ? (new \DateTime($oResult->dLastRun))->format('Y-m-d H:i:s')
            : null;

        //$this->oLogger->debug('NOW: '. (new \DateTime())->format('Y-m-d H:i:s')); // --DEBUG--
        //$this->oLogger->debug('szLastRun(1): '.var_export($this->szLastRun,true)); // --DEBUG--

        if (null === $this->szLastRun) {
            $this->szLastRun = (new \DateTime())->format('Y-m-d H:i:s');
        }
        //$this->oLogger->debug('szLastRun(2): '.print_r($this->szLastRun,true )); // --DEBUG--


        // --DEVELOPMENT-- fake-date !!! change to now: "DateTime()"
        //$oTimeDistance = (new \DateTime())->diff(new \DateTime($this->szLastRun));
        $oTimeDistance = (new \DateTime('2018-07-01 10:10:10'))->diff(new \DateTime($this->szLastRun));
        $this->oLogger->debug('DateInterval (days): '.print_r($oTimeDistance->d, true ).' days'); // --DEBUG--

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


/* {{{
        // last run + 30
        $szTargetDate = (new \DateTime($this->szLastRun))
            ->add(new \DateInterval('P'.\GdprAnonymizing\Intervals::TC_DAYS_30.'D'))
            ->format('Y-m-d H:i:s');
        $this->oLogger->debug('target (30): '.$szTargetDate); // --DEBUG--
        //
        // last run + 90
        $szTargetDate = (new \DateTime($this->szLastRun))
            ->add(new \DateInterval('P'.\GdprAnonymizing\Intervals::TC_DAYS_90.'D'))
            ->format('Y-m-d H:i:s');
        $this->oLogger->debug('target (90): '.$szTargetDate); // --DEBUG--
        //
        // $this->szLastRun + \GdprAnonymizing\Intervals::TC_DELETEGUESTS;
        // last run + 365
        $szTargetDate = (new \DateTime($this->szLastRun))
            ->add(new \DateInterval('P'.\GdprAnonymizing\Intervals::TC_DELETEGUESTS.'D'))
            ->format('Y-m-d H:i:s');
        $this->oLogger->debug('target (365): '.$szTargetDate); // --DEBUG--
        //
        // anonymize at the end of year which is followed by the year of creation ... --TODO--
        // (from data/DB)
        //
        $iInputDateYear = (int)(new \DateTime())->format('Y');
        $oInputDate = new \DateTime((string)(++$iInputDateYear).'-12-31 23:59:59');
        $this->oLogger->debug('end of next year: '.$oInputDate->format('Y-m-d H:i:s')); // --DEBUG--
}}} */
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
