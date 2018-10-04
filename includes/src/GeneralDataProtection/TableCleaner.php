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
     * anonymize-methods
     * (NOTE: the order of this methods is not insignificant and can be configured)
     *
     * @var array
     */
    private $vMethods = [
        ['szName' => 'AnonymizeIps', 'iIntervalDays' => 7],
        ['szName' => 'AnonymizeDeletedCustomer', 'iIntervalDays' => 7],
        ['szName' => 'CleanupCustomerRelicts', 'iIntervalDays' => 0],
        ['szName' => 'CleanupGuestAccountsWithoutOrders', 'iIntervalDays' => 0],
        ['szName' => 'CleanupNewsletterRecipients', 'iIntervalDays' => 30],
        ['szName' => 'CleanupLogs', 'iIntervalDays' => 90],
        ['szName' => 'CleanupOldGuestAccounts', 'iIntervalDays' => 365]
    ];

    /**
     * @var object Monolog\Logger
     */
    private $oLogger;


    public function __construct()
    {
        // get the main-logger
        try {
            $this->oLogger = \Shop::Container()->getLogService();
        } catch (\Exception $e) {
            $this->oLogger = null;
        }
        // set the DateTime which this object/package uses
        $this->oNow = new \DateTime();
    }

    public function execute()
    {
        $t_start = microtime(true); // runtime-measurement

        $nMethodsCount = \count($this->vMethods);
        for ($i=0; $i < $nMethodsCount ; $i++) {
            $szMethodName = __NAMESPACE__ . '\\' . $this->vMethods[$i]['szName'];
            ($this->oLogger === null) ?: $this->oLogger->log(JTLLOG_LEVEL_NOTICE, 'Anonymize Method running: ' . $this->vMethods[$i]['szName']);
            (new $szMethodName($this->oNow, $this->vMethods[$i]['iIntervalDays']))->execute();
        }

        $t_elapsed = microtime(true) - $t_start; // runtime-measurement
        ($this->oLogger === null) ?: $this->oLogger->log(JTLLOG_LEVEL_NOTICE, 'Anonymizing was finished in: ' . sprintf('%01.4fs', $t_elapsed));
    }

    public function __destruct()
    {
        // tidy up the journal (`tanondatajournal`).
        // removes entries older than one year after their creation.
        \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tanondatajournal`
            WHERE `dEventTime` <= LAST_DAY(DATE_ADD(NOW() - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(NOW()) MONTH))',
            [],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

}

