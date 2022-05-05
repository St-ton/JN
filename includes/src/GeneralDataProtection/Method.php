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
    protected $now;

    /**
     * interval in "number of days"
     *
     * @var int
     */
    protected $interval = 0;

    /**
     * select the maximum of 1000 rows for one "step per task"!
     * (CONSIDER: some tasks need to overwrite this!)
     *
     * @var int
     */
    protected $workLimit = 1000;

    /**
     * summery of processed entities per method
     *
     * @var integer
     */
    protected $workSum = 0;

    /**
     * is this task finished
     *
     * @var boolean
     */
    protected $isFinished = false;

    /**
     * the last date we keep
     * (depends on interval)
     *
     * @var string
     */
    protected $dateLimit;

    /**
     * main shop logger
     *
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @param DateTime    $now
     * @param int         $interval
     * @param DbInterface $db
     */
    public function __construct(DateTime $now, int $interval, DbInterface $db)
    {
        try {
            $this->logger = Shop::Container()->getLogService();
        } catch (Exception $e) {
            $this->logger = null;
        }
        $this->db       = $db;
        $this->now      = clone $now;
        $this->interval = $interval;
        try {
            $this->dateLimit = $this->now->sub(
                new DateInterval('P' . $this->interval . 'D')
            )->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            ($this->logger === null) ?: $this->logger->log(
                \JTLLOG_LEVEL_WARNING,
                'Wrong Interval given: ' . $this->interval
            );
        }
    }

    /**
     * deliver the state of a method
     *
     * @return boolean
     */
    public function getIsFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * deliver the summery of processed entities in method
     *
     * @return integer
     */
    public function getWorkSum(): int
    {
        return $this->workSum;
    }
}
