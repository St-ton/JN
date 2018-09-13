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
        // anonymize deleted or canceled customers
        (new \GeneralDataProtection\AnonymizeDeletedCustomer(
            $this->oNow,
            \GeneralDataProtection\Intervals::vTIMERS['INTERVAL_DAYS_7']
        ))->execute();

        // Delete customer relicts in logs and sub-tables
        // and delete shipping and billing-addresses of deleted customers
        (new \GeneralDataProtection\CleanupCustomerRelicts(
            $this->oNow,
            \GeneralDataProtection\Intervals::vTIMERS['INTERVAL_DAYS_0']
        ))->execute();

        // delete guest-accounts with no open orders
        (new \GeneralDataProtection\CleanupDeletedGuestAccounts(
            $this->oNow,
            \GeneralDataProtection\Intervals::vTIMERS['INTERVAL_DAYS_0']
        ))->execute();


        // Delete newsletter-registrations with no opt-in within given interval
        (new \GeneralDataProtection\CleanupNewsletterRecipients(
            $this->oNow,
            \GeneralDataProtection\Intervals::vTIMERS['INTERVAL_DAYS_30']
        ))->execute();

        // Delete old logs containing personal data.
        // (Customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu))
        (new \GeneralDataProtection\CleanupLogs(
            $this->oNow,
            \GeneralDataProtection\Intervals::vTIMERS['INTERVAL_DAYS_90']
        ))->execute();

        // Remove guest accounts fetched by JTL-Wawi and older than x days
        (new \GeneralDataProtection\CleanupOldGuestAccounts(
            $this->oNow,
            \GeneralDataProtection\Intervals::vTIMERS['INTERVAL_DAYS_365']
        ))->execute();

    }

    public function __destruct()
    {
        // tidy up the journal (`tanondatajournal`).
        // remove entries older than one year after their creation.
        $vResult = \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tanondatajournal`
            WHERE `dEventTime` <= LAST_DAY(DATE_ADD(NOW() - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(NOW()) MONTH))',
            [],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

}
