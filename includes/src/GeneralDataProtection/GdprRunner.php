<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * class GdprController
 * ("Global Data Protection Rules", german: "DSGVO")
 *
 * New periods can be added in class "Intervals",
 * in the constants array "vTIMERS".
 * Then add a method here, with the name
 * "period_TIMERNAME", to declare,
 * what has to be done periodically.
 */
class GdprRunner
{
    /**
     * the "now"-date, with wich this object works
     * @var DateTime
     */
    private $dNow = null;

    /**
     * all timers (and their last-run time) for the different periods
     * @var array
     */
    private $vTimersLastRun = [];


    private $oLogger = null; // --DEBUG--

    public function __construct()
    {
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--
        include_once('/var/www/html/shop4_07/includes/vendor/apache/log4php/src/main/php/Logger.php');
        \Logger::configure('/var/www/html/shop4_07/_logging_conf.xml');
        $this->oLogger = \Logger::getLogger('default');
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --DEBUG--


        // set the "now" of this object
        $this->dNow = new \DateTime();

        // DateTime tests ... we go artificially into the future
//$this->dNow = new \DateTime('2018-07-01 00:00:00'); // --DEVELOPMENT-- "fake today"
//$this->dNow = new \DateTime('2018-08-17 00:00:00'); // --DEVELOPMENT-- "fake today"
//$this->dNow = new \DateTime('2018-12-15 00:00:00'); // --DEVELOPMENT-- "fake today"
//$this->oLogger->debug('plus X days: '.(new \DateTime())->add(new \DateInterval('P8D'))->format('Y-m-d H:i:s')); // --DEBUG--
//$this->dNow = (new \DateTime())->add(new \DateInterval('P8D')); // --DEVELOPMENT-- "fake today" plus >7 days
//$this->dNow = (new \DateTime())->add(new \DateInterval('P35D')); // --DEVELOPMENT-- "fake today" plus >30 days
//$this->dNow = (new \DateTime())->add(new \DateInterval('P98D')); // --DEVELOPMENT-- "fake today" plus >90 days
$this->dNow = (new \DateTime())->add(new \DateInterval('P202D')); // --DEVELOPMENT-- "fake today" plus >90 days
//$this->dNow = (new \DateTime())->add(new \DateInterval('P370D')); // --DEVELOPMENT-- "fake today" plus >365 days
        $this->oLogger->debug('this->dNow: '.$this->dNow->format('Y-m-d H:i:s')); // --DEBUG--

        $this->buildTimers();
    }

    /**
     * load / rebuild all timers for all periods
     */
    private function buildTimers()
    {
        $oResult = \Shop::Container()->getDB()->queryPrepared(
            'SELECT * FROM `tgdprtimers`', [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (false !== $oResult) {
            // build a handable array
            for ($i = 0; $i < sizeof($oResult); $i++) {
                $this->vTimersLastRun[$oResult[$i]->cTimerName] = $oResult[$i]->dTimerLastRun;
            }
        }
        // checking, if we really have a timer in our DB for each interval
        foreach (\GeneralDataProtection\Intervals::vTIMERS as $szTimerName => $szTimerLimit) {
            if (!isset($this->vTimersLastRun[$szTimerName])) {
                // if that timer did not exist in our DB,
                // we "create" and insert them with the current DateTime
                $this->vTimersLastRun[$szTimerName] = $this->dNow->format('Y-m-d H:i:s');
                $oResult = \Shop::Container()->getDB()->queryPrepared(
                    'INSERT INTO `tgdprtimers` VALUES(:tname, :tvalue)',
                    ['tname' => $szTimerName, 'tvalue' => $this->vTimersLastRun[$szTimerName]],
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                );
            }
        }
    }

    /**
     * executes all our tasks (by timer-name),
     * if their periods are ran out
     */
    public function execute()
    {
        //$this->oLogger->debug('vTIMERS: '.print_r(\GeneralDataProtection\Intervals::vTIMERS,true )); // --DEBUG--

        // select the next lower period for execution (execute NEVER ALL!)
        $vExecutionStack = [];
        foreach (\GeneralDataProtection\Intervals::vTIMERS as $szTimerName => $szTimerLimit) {
            $oDiff = (new \DateTime($this->vTimersLastRun[$szTimerName]))->diff($this->dNow);
            if ($oDiff->days > \GeneralDataProtection\Intervals::vTIMERS[$szTimerName]) {
                //$this->oLogger->debug($szTimerName.' ('.$szTimerLimit.')'); // --DEBUG--
                $vExecutionStack[$szTimerLimit] = $szTimerName;
            }
        }
        $this->oLogger->debug('vExecutionStack: '.print_r($vExecutionStack,true )); // --DEBUG--
        if (0 < sizeof($vExecutionStack)) {
            $szMethode = 'period_'.array_pop($vExecutionStack);
            //$this->oLogger->debug('will execute: '.$szMethode.'()'); // --DEBUG--
            $this->$szMethode();
            $this->resetTimerDb($szTimerName);
        }

        /*
        foreach (\GeneralDataProtection\Intervals::vTIMERS as $szTimerName => $szTimerLimit)
        {
            $oDiff = (new \DateTime($this->vTimersLastRun[$szTimerName]))->diff($this->dNow);
            $this->oLogger->debug('DIFF->days: '.print_r($oDiff->days ,true )); // --DEBUG--
            if ($oDiff->days > \GeneralDataProtection\Intervals::vTIMERS[$szTimerName]) {
                $szMethode = 'period_'.$szTimerName;
                $this->$szMethode();
                $this->resetTimerDb($szTimerName);
            }
        }
        */

        $this->oLogger->debug('- - - - - - - - - - - - - - - - - - - - '); // --DEBUG--
    }

    /**
     * reset the dTimerLastRun to "now()"
     *
     * @param string szTimerName
     * @return bool
     */
    private function resetTimerDb(string $szTimerName) : bool
    {
        return \Shop::Container()->getDB()->queryPrepared(
            'UPDATE `tgdprtimers` SET `dTimerLastRun` = now() WHERE `cTimerName` = :timername'
            , ['timername' => $szTimerName]
            , \DB\ReturnType::SINGLE_OBJECT
        );
    }

    private function period_TC_DAYS_7()
    {
        $this->oLogger->debug('run 7 ...'); // --DEBUG--

        // ----------- every 7 days
        //
        // anonymize IPs each 7 days (except these, which was anonymized immediately)

        // --TO-CHECK--
        // for now:
        // anon all IPs, which has to be anonymized the end of the next year after there creation
        // anon in multiple tables
        //
       //$oMethod = (new \GeneralDataProtection\AnonymizeIps())->execute();

        // --TO-CHECK--
        // anon `tbewertung`, `tzahlungseingang`, `tnewskommentar`
        // (no intervals, update only)
        //
        $oMethod = (new \GeneralDataProtection\AnonymizeDeletedCustomer())
            ->execute(\GeneralDataProtection\Intervals::iNOINTERVAL);

        // Delete customer relicts in logs and subtables and delete shipping and billing-addresses of deleted customers
        // (no intervals, removing only)
        //
        $oMethod = (new \GeneralDataProtection\CleanupCustomerRelicts())
            ->execute(\GeneralDataProtection\Intervals::iNOINTERVAL);

        // delete guest-accounts with no open orders (no interval, each call)
        // (no intervals, removing only)
        //
        $oMethod = (new \GeneralDataProtection\CleanupDeletedGuestAccounts())
            ->execute(\GeneralDataProtection\Intervals::iNOINTERVAL);


        // anonymize at the end of year which is followed by the year of creation ... --TODO--
        // (from data/DB)
        //
        //$iInputDateYear = (int)(new \DateTime())->format('Y');
        //$oInputDate = new \DateTime((string)(++$iInputDateYear).'-12-31 23:59:59');
        //$this->oLogger->debug('end of next year: '.$oInputDate->format('Y-m-d H:i:s')); // --DEBUG--

        // --TODO-- tfsession ? truncat
    }

    private function period_TC_DAYS_30()
    {
        $this->oLogger->debug('run 30 ...'); // --DEBUG--

        // ----------- every 30 days

        // Delete newsletter-registrations with no opt-in within given interval
        // (INTERVAL! removing by interval)
        // `tnewsletterempfaenger`

        $oAction = new \GeneralDataProtection\CleanupNewsletterRecipients();
        $oAction->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_30']);

    }

    private function period_TC_DAYS_90()
    {
        $this->oLogger->debug('run 90 ...'); // --DEBUG--

        // ----------- every 90 days

        // Delete old logs containing personal data.
        // (Customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu))
        // (INTERVALS! removing by interval)

        $oMethod = new \GeneralDataProtection\CleanupLogs();
        $oMethod->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_90']);

    }

    private function period_TC_DAYS_365()
    {
        $this->oLogger->debug('run 365 ...'); // --DEBUG--

        // ----------- every 365 days

        // Remove guest accounts fetched by JTL-Wawi and older than x days
        // (INTERVALS, removing by interval)

        $oMethod = new \GeneralDataProtection\CleanupOldGuestAccounts();
        $oMethod->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_365']);

    }

}
