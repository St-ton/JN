<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use DateInterval;
use DateTime;
use Exception;
use JTL\DB\DbInterface;
use JTL\Shop;
use Psr\Log\LoggerInterface;

/**
 * Class Method
 * @package JTL\GeneralDataProtection
 */
class Method
{
    /**
     * object wide date at the point of instantiating
     *
     * @var DateTime
     */
    protected DateTime $now;

    /**
     * select the maximum of 1000 rows for one "step per task"!
     * (CONSIDER: some tasks need to overwrite this!)
     *
     * @var int
     */
    protected int $workLimit = 1000;

    /**
     * summery of processed entities per method
     *
     * @var int
     */
    protected $workSum = 0;

    /**
     * is this task finished
     *
     * @var bool
     */
    protected $isFinished = false;

    /**
     * max repetitions of one task
     * (can be overridden in each task)
     *
     * @var int
     */
    protected $taskRepetitions = 0;

    /**
     * last ID for `CleanupGuestAccountsWithoutOrders`
     *
     * @var int
     */
    protected $lastProductID = 0;

    /**
     * the last date we keep
     * (depends on interval)
     *
     * @var string|null
     */
    protected ?string $dateLimit = null;

    /**
     * main shop logger
     *
     * @var LoggerInterface|null
     */
    protected ?LoggerInterface $logger;

    /**
     * @param DateTime    $now
     * @param int         $interval
     * @param DbInterface $db
     */
    public function __construct(DateTime $now, protected int $interval, protected DbInterface $db)
    {
        try {
            $this->logger = Shop::Container()->getLogService();
        } catch (Exception) {
            $this->logger = null;
        }
        $this->now = clone $now;
        try {
            $this->dateLimit = $this->now->sub(
                new DateInterval('P' . $this->interval . 'D')
            )->format('Y-m-d H:i:s');
        } catch (Exception) {
            $this->logger?->warning('Wrong interval given: {interval}', ['interval' => $this->interval]);
        }
    }

    /**
     * deliver the state of a method
     *
     * @return bool
     */
    public function getIsFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * deliver the summery of processed entities in method
     *
     * @return int
     */
    public function getWorkSum(): int
    {
        return $this->workSum;
    }

    /**
     * deliver the max alowed repetition of one task
     *
     * @return int
     */
    public function getTaskRepetitions(): int
    {
        return $this->taskRepetitions;
    }

    /**
     * deliver the last ID in table (CleanupGuestAccountsWithoutOrders)
     *
     * @return int
     */
    public function getLastProductID(): int
    {
        return $this->lastProductID ?? 0;
    }

    /**
     * set the last processed tupel ID of a table
     *
     * @param int $lastProductID
     * @return void
     */
    public function setLastProductID(int $lastProductID): void
    {
        $this->lastProductID = $lastProductID;
    }
}
