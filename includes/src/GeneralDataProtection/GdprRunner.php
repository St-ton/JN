<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * class GdprController
 * ("Global Data Protection Rules", german: "DSGVO")
 */
class GdprRunner
{
    /**
     * the "now"-date, with wich this object works
     * @var DateTime
     */
    private $oNow = null;

    public function __construct()
    {
        // set the "now" of this object
        $this->oNow = new \DateTime();
    }

    public function execute()
    {

        (new \GeneralDataProtection\AnonymizeDeletedCustomer(
            $this->oNow,
            \GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_7']
        ))->execute();

        // Delete customer relicts in logs and subtables and delete shipping and billing-addresses of deleted customers
        // (no intervals, removing only)
        //
        (new \GeneralDataProtection\CleanupCustomerRelicts(
            $this->oNow,
            \GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_7']
        ))->execute();

        // delete guest-accounts with no open orders (no interval, each call)
        // (no intervals, removing only)
        //
//        (new \GeneralDataProtection\CleanupDeletedGuestAccounts())
//            ->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_7']);


        // ----------- every 30 days
        // Delete newsletter-registrations with no opt-in within given interval
        // (INTERVAL! removing by interval)
        // `tnewsletterempfaenger`
        //
//        $oAction = new \GeneralDataProtection\CleanupNewsletterRecipients();
//        $oAction->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_30']);

        // ----------- every 90 days
        // Delete old logs containing personal data.
        // (Customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu))
        // (INTERVALS! removing by interval)
        //
//        $oMethod = new \GeneralDataProtection\CleanupLogs();
//        $oMethod->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_90']);

        // ----------- every 365 days
        // Remove guest accounts fetched by JTL-Wawi and older than x days
        // (INTERVALS, removing by interval)
        //
//        $oMethod = new \GeneralDataProtection\CleanupOldGuestAccounts();
//        $oMethod->execute(\GeneralDataProtection\Intervals::vTIMERS['TC_DAYS_365']);

    }


}
