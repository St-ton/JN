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
     * select the maximum of 1,000 rows for one step!
     * (if the scripts are running each day, we need some days
     * to anonymize more than 1,000 data sets)
     *
     * @var int
     */
    protected int $workLimit = 1000;

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
            ($this->logger === null) ?: $this->logger->log(
                \JTLLOG_LEVEL_WARNING,
                'Wrong Interval given: ' . $this->interval
            );
        }
    }
}
