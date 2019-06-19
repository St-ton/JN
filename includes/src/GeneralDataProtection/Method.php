<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\GeneralDataProtection;

use JTL\Shop;

/**
 * Class Method
 * @package JTL\GeneralDataProtection
 */
class Method
{
    /**
     * object-wide date at the point of instanciating
     *
     * @var object DateTime
     */
    protected $now;

    /**
     * interval in "number of days"
     *
     * @var int
     */
    protected $interval = 0;

    /**
     * select the maximum of 10,000 rows for one step!
     * (if the scripts are running each day, we need some days
     * to anonymize more than 10,000 data sets)
     *
     * @var int
     */
    protected $workLimit = 10000;

    /**
     * the last date we keep
     * (depending from interval)
     *
     * @var string
     */
    protected $dateLimit;

    /**
     * main shop logger
     *
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Method constructor.
     * @param \DateTime $now
     * @param int       $interval
     */
    public function __construct(\DateTime $now, int $interval)
    {
        try {
            $this->logger = Shop::Container()->getLogService();
        } catch (\Exception $e) {
            $this->logger = null;
        }
        $this->now      = clone $now;
        $this->interval = $interval;
        try {
            $this->dateLimit = $this->now->sub(
                new \DateInterval('P' . $this->interval . 'D')
            )->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            ($this->logger === null) ?: $this->logger->log(
                \JTLLOG_LEVEL_WARNING,
                'Wrong Interval given: ' . $this->interval
            );
        }
    }
}
