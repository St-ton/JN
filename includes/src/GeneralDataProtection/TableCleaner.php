<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * class TableCleaner
 * controller-class of "shop customer data anonymization"
 * ("GDPR" or "Global Data Protection Rules", german: "DSGVO")
 */
class TableCleaner
{
    /**
     * object-wide date at the point of instanciating
     *
     * @var object DateTime
     */
    private $oNow;

    /**
     * @var object Monolog\Logger
     */
    private $oLogger;

    /**
     * anonymize-methods
     * (NOTE: the order of this methods is not insignificant and "can be configured")
     *
     * @var array
     */
    private $vMethods = [
        ['szName' => 'AnonymizeIps',                      'iIntervalDays' => 7],
        ['szName' => 'AnonymizeDeletedCustomer',          'iIntervalDays' => 7],
        ['szName' => 'CleanupCustomerRelicts',            'iIntervalDays' => 0],
        ['szName' => 'CleanupGuestAccountsWithoutOrders', 'iIntervalDays' => 0],
        ['szName' => 'CleanupNewsletterRecipients',       'iIntervalDays' => 30],
        ['szName' => 'CleanupLogs',                       'iIntervalDays' => 90],
        ['szName' => 'CleanupOldGuestAccounts',           'iIntervalDays' => 365],
        ['szName' => 'CleanupService',                    'iIntervalDays' => 0] // multiple own intervals
    ];


    public function __construct()
    {
        // get the main-logger
        try {
            $this->oLogger = \Shop::Container()->getLogService();
        } catch (\Exception $e) {
            $this->oLogger = null;
        }
        // sets the time which has to be used by all sub-processes
        $this->oNow = new \DateTime();
    }

    /**
     * run all anonymize and clean-up methods
     */
    public function execute()
    {
        $nTimeStart    = microtime(true); // runtime-measurement
        $nMethodsCount = \count($this->vMethods);
        $szLogSum      = '';
        // iterate over the indexed array (configurable order!)
        for ($i=0; $i < $nMethodsCount ; $i++) {
            $szMethodName = __NAMESPACE__ . '\\' . $this->vMethods[$i]['szName'];
            (new $szMethodName($this->oNow, $this->vMethods[$i]['iIntervalDays']))->execute();
            $szLogSum .= 'Anonymize Method executed: ' . $this->vMethods[$i]['szName'] . "\n";
        }
        $nTimeElapsed = microtime(true) - $nTimeStart; // runtime-measurement
        $this->_Logger->debug('ELAPSED TIME: '.sprintf('%01.4fs', $nTimeElapsed)); // --DEBUG--
        ($this->oLogger === null) ?: $this->oLogger->log(JTLLOG_LEVEL_NOTICE,
            $szLogSum . "\n" .
            'Anonymizing was finished in: ' . sprintf('%01.4fs', $nTimeElapsed)
        );
    }

    /**
     * tidy up the journal
     */
    public function __destruct()
    {
        // removes journal-entries at the end of next year after their creation
        \Shop::Container()->getDB()->queryPrepared('DELETE FROM tanondatajournal
            WHERE dEventTime <= LAST_DAY(DATE_ADD(:pNow - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(:pNow) MONTH))',
            [
                'pNow' => $this->oNow->format('Y-m-d H:i:s')
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

}

