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


    public function __construct()
    {
        // set the "now" of this object
        $this->dNow = new \DateTime();

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
        // select the next lower period for execution (execute NEVER ALL!)
        $vExecutionStack = [];
        foreach (\GeneralDataProtection\Intervals::vTIMERS as $szTimerName => $szTimerLimit) {
            $oDiff = (new \DateTime($this->vTimersLastRun[$szTimerName]))->diff($this->dNow);
            if ($oDiff->days > \GeneralDataProtection\Intervals::vTIMERS[$szTimerName]) {
                $vExecutionStack[$szTimerLimit] = $szTimerName;
            }
        }
        if (0 < sizeof($vExecutionStack)) {
            $szMethode = 'period_'.array_pop($vExecutionStack);
            $this->$szMethode();
            $this->resetTimerDb($szTimerName);
        }

        /*
        foreach (\GeneralDataProtection\Intervals::vTIMERS as $szTimerName => $szTimerLimit)
        {
            $oDiff = (new \DateTime($this->vTimersLastRun[$szTimerName]))->diff($this->dNow);
            if ($oDiff->days > \GeneralDataProtection\Intervals::vTIMERS[$szTimerName]) {
                $szMethode = 'period_'.$szTimerName;
                $this->$szMethode();
                $this->resetTimerDb($szTimerName);
            }
        }
        */
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

        // --TODO-- tfsession ? truncat
    }

    private function period_TC_DAYS_30()
    {
        // ----------- every 30 days
        // 
        // Delete newsletter-registrations with no opt-in within given interval
        // (INTERVAL! removing by interval)
        // `tnewsletterempfaenger`

        $oAction = new \GeneralDataProtection\CleanupNewsletterRecipients();
        $oAction->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_30']);

    }

    private function period_TC_DAYS_90()
    {
        // ----------- every 90 days
        //
        // Delete old logs containing personal data.
        // (Customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu))
        // (INTERVALS! removing by interval)

        $oMethod = new \GeneralDataProtection\CleanupLogs();
        $oMethod->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_90']);

    }

    private function period_TC_DAYS_365()
    {
        // ----------- every 365 days
        //
        // Remove guest accounts fetched by JTL-Wawi and older than x days
        // (INTERVALS, removing by interval)

        $oMethod = new \GeneralDataProtection\CleanupOldGuestAccounts();
        $oMethod->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_365']);

    }

}
