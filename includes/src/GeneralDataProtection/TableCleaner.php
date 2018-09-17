<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * class TableCleaner
 * controller-class of shop-anonymization
 * ("Global Data Protection Rules", german: "DSGVO")
 *
 * New periods can be added in class "Intervals",
 * in the constants array "vTIMERS".
 */
class TableCleaner
{
    /**
     * @var DateTime
     * object-wide date at the point of instanciating
     */
    private $oNow = null;

    /**
     * anonymize-methods
     */
    private $vMethodes = [
        'AnonymizeIps'                       => Intervals::vTIMERS['INTERVAL_DAYS_365']
        'AnonymizeDeletedCustomer'           => Intervals::vTIMERS['INTERVAL_DAYS_7'],
        'CleanupCustomerRelicts'             => Intervals::vTIMERS['INTERVAL_DAYS_0'],
        'CleanupGuestAccountsWhithoutOrders' => Intervals::vTIMERS['INTERVAL_DAYS_0'],
        'CleanupNewsletterRecipients'        => Intervals::vTIMERS['INTERVAL_DAYS_30'],
        'CleanupLogs'                        => Intervals::vTIMERS['INTERVAL_DAYS_90'],
        'CleanupOldGuestAccounts'            => Intervals::vTIMERS['INTERVAL_DAYS_365']
    ];

    public function __construct()
    {
        // set the "now" of this object
        $this->oNow = new \DateTime();
    }

    public function execute()
    {
        foreach ($this->vMethodes as $szMethod => $iTiming) {
            $szMethodName = __NAMESPACE__ . '\\' . $szMethod;
            (new $szMethodName($this->oNow, $iTiming))->execute(); 
        }
    }

    public function __destruct()
    {
        // tidy up the journal (`tanondatajournal`).
        // removes entries older than one year after their creation.
        $vResult = \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tanondatajournal`
            WHERE `dEventTime` <= LAST_DAY(DATE_ADD(NOW() - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(NOW()) MONTH))',
            [],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

}
