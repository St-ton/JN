<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use DateTime;
use Exception;
use JTL\DB\DbInterface;
use JTL\Shop;
use Psr\Log\LoggerInterface;

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
     * @var DateTime
     */
    private DateTime $now;

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var boolean
     */
    private $isFinished = true;

    /**
     * @var int
     */
    private $taskRepetitions;

    /**
     * @var int
     */
    private $lastProductID;


    /**
     * anonymize methods
     * (NOTE: the order of this methods is not insignificant and "can be configured")
     *
     * @var array
     */
    private array $methods = [
        ['name' => 'AnonymizeIps'                      , 'intervalDays' => 7],
        ['name' => 'AnonymizeDeletedCustomer'          , 'intervalDays' => 7],
        ['name' => 'CleanupCustomerRelicts'            , 'intervalDays' => 0],
        ['name' => 'CleanupNewsletterRecipients'       , 'intervalDays' => 30],
        ['name' => 'CleanupLogs'                       , 'intervalDays' => 90],
        ['name' => 'CleanupService'                    , 'intervalDays' => 0],  // multiple own intervals
        ['name' => 'CleanupForgottenOptins'            , 'intervalDays' => 1],  // same as 24 hours
        ['name' => 'CleanupGuestAccountsWithoutOrders' , 'intervalDays' => 0]
    ];

    /**
     * TableCleaner constructor.
     * @throws Exception
     */
    public function __construct()
    {
        try {
            $this->logger = Shop::Container()->getLogService();
        } catch (Exception) {
            $this->logger = null;
        }
        $this->db  = Shop::Container()->getDB();
        $this->now = new DateTime();
    }

    /**
     * get the count of all methods
     *
     * @return int
     */
    public function getMethodCount(): int
    {
        return \count($this->methods);
    }

    /**
     * tells upper processes "this task is unfinished"
     *
     * @return boolean
     */
    public function getIsFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * tells upper processes the max repetition count of one task
     *
     * @return int
     */
    public function getTaskRepetitions(): int
    {
        return $this->taskRepetitions;
    }

    /**
     * tells upper processes the max ID in table
     *
     * @return int
     */
    public function getLastProductID(): int
    {
        return $this->lastProductID;
    }

    /**
     * execute one single job by its index number
     *
     * @param int $taskIdx
     * @param int $taskRepetitions
     * @param int $lastProductID
     * @return void
     */
    public function executeByStep(int $taskIdx, int $taskRepetitions, int $lastProductID): void
    {
        $this->lastProductID = $lastProductID;
        if ($taskIdx < 0 || $taskIdx > \count($this->methods)) {
            $this->logger?->notice('GeneralDataProtection: No task ID given.');
            return;
        }
        $methodName = __NAMESPACE__ . '\\' . $this->methods[$taskIdx]['name'];
        /** @var MethodInterface $instance */
        $instance = new $methodName($this->now, $this->methods[$taskIdx]['intervalDays'], $this->db);
        // repetition-value from DB has preference over task-setting!
        if ($taskRepetitions !== 0) {
            // override the repetition-value of the instance
            $instance->taskRepetitions = $taskRepetitions;
            $this->taskRepetitions     = $taskRepetitions;
        } else {
            $this->taskRepetitions = $instance->getTaskRepetitions();
        }
        $instance->setLastProductID($this->lastProductID);
        $instance->execute();
        $this->taskRepetitions = $instance->getTaskRepetitions();
        $this->lastProductID   = $instance->getLastProductID();
        $this->isFinished      = $instance->getIsFinished();
        $this->logger?->notice(
            'Anonymize method executed: {name}, {cnt} entities processed.',
            ['name' => $this->methods[$taskIdx]['name'], 'cnt' => $instance->getWorkSum()]
        );
    }

    /**
     * run all anonymize and clean up methods
     *
     * @return void
     */
    public function executeAll(): void
    {
        $timeStart = \microtime(true);
        foreach ($this->methods as $method) {
            $methodName = __NAMESPACE__ . '\\' . $method['name'];
            /** @var MethodInterface $instance */
            $instance = new $methodName($this->now, $method['intervalDays'], $this->db);
            $instance->execute();
            $this->logger?->notice('Anonymize method executed: {method}', ['method' => $method['name']]);
        }
        $this->logger?->notice('Anonymizing finished in: ' . \sprintf('%01.4fs', \microtime(true) - $timeStart));
    }

    /**
     * tidy up the journal
     */
    public function __destruct()
    {
        // removes journal-entries at the end of next year after their creation
        $this->db->queryPrepared(
            'DELETE FROM tanondatajournal
                WHERE dEventTime <= LAST_DAY(DATE_ADD(:pNow - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(:pNow) MONTH))',
            ['pNow' => $this->now->format('Y-m-d H:i:s')]
        );
    }
}
