<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\GeneralDataProtection;

use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * Class TableCleaner
 * @package JTL\GeneralDataProtection
 *
 * controller of "shop customer data anonymization"
 * ("GDPR" or "Global Data Protection Rules", german: "DSGVO")
 */
class TableCleaner
{
    /**
     * object wide date at the point of instanciating
     *
     * @var object DateTime
     */
    private $now;

    /**
     * @var object Monolog\Logger
     */
    private $logger;

    /**
     * anonymize methods
     * (NOTE: the order of this methods is not insignificant and "can be configured")
     *
     * @var array
     */
    private $methods = [
        ['szName' => 'AnonymizeIps', 'intervalDays' => 7],
        ['szName' => 'AnonymizeDeletedCustomer', 'intervalDays' => 7],
        ['szName' => 'CleanupOldGuestAccounts', 'intervalDays' => 365],
        ['szName' => 'CleanupCustomerRelicts', 'intervalDays' => 0],
        ['szName' => 'CleanupGuestAccountsWithoutOrders', 'intervalDays' => 0],
        ['szName' => 'CleanupNewsletterRecipients', 'intervalDays' => 30],
        ['szName' => 'CleanupLogs', 'intervalDays' => 90],
        ['szName' => 'CleanupService', 'intervalDays' => 0] // multiple own intervals
    ];

    /**
     * TableCleaner constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        try {
            $this->logger = Shop::Container()->getLogService();
        } catch (\Exception $e) {
            $this->logger = null;
        }
        // sets the time which has to be used by all sub-processes
        $this->now = new \DateTime();
    }

    /**
     * run all anonymize and clean up methods
     */
    public function execute(): void
    {
        $timeStart   = \microtime(true); // runtime-measurement
        $methodCount = \count($this->methods);
        // iterate over the indexed array (configurable order!)
        for ($i = 0; $i < $methodCount; $i++) {
            $methodName = __NAMESPACE__ . '\\' . $this->methods[$i]['szName'];
            (new $methodName($this->now, $this->methods[$i]['intervalDays']))->execute();
            ($this->logger === null) ?: $this->logger->log(
                \JTLLOG_LEVEL_NOTICE,
                'Anonymize method executed: ' . $this->methods[$i]['szName']
            );
        }
        ($this->logger === null) ?: $this->logger->log(
            \JTLLOG_LEVEL_NOTICE,
            'Anonymizing finished in: ' . \sprintf('%01.4fs', \microtime(true) - $timeStart)
        );
    }

    /**
     * tidy up the journal
     */
    public function __destruct()
    {
        // removes journal-entries at the end of next year after their creation
        Shop::Container()->getDB()->queryPrepared(
            'DELETE FROM tanondatajournal
            WHERE dEventTime <= LAST_DAY(DATE_ADD(:pNow - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(:pNow) MONTH))',
            ['pNow' => $this->now->format('Y-m-d H:i:s')],
            ReturnType::DEFAULT
        );
    }
}
