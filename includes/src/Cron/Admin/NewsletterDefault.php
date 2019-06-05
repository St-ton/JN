<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Admin;

use JTL\Shop;

/**
 * Class NewsletterDefault
 * @package JTL\Cron\Job
 */
class NewsletterDefault
{

    /**
     * @var null
     */
    public $cronID = null;

    /**
     * @var null
     */
    public $foreignKeyID = 0;

    /**
     * @var string
     */
    public $foreignKey = 'kNewsletter';

    /**
     * @var string
     */
    public $tableName = 'tnewsletter';

    /**
     * @var string
     */
    public $name = 'Newsletter';

    /**
     * @var string
     */
    public $jobType = 'newsletter';

    /**
     * @var int
     */
    public $frequency = 2;

    /**
     * @var null
     */
    public $startDate = null;

    /**
     * @var null
     */
    public $startTime = null;

    /**
     * @var null
     */
    public $lastStart = null;

    /**
     * @var null
     */
    public $lastFinish = null;

    /**
     * NewsletterDefault constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->startDate  = (new \DateTime())->format('Y-m-d H:i:s');
        $this->startTime  = (new \DateTime())->format('H:i:s');
        $this->lastStart  = '_DBNULL_';
        $this->lastFinish = '_DBNULL_';
        $this->frequency  = Shop::getConfigValue(\CONF_NEWSLETTER, 'newsletter_send_delay');
    }

    /**
     * @return null
     */
    public function getForeignKeyID()
    {
        return $this->foreignKeyID;
    }

    /**
     * @param null $foreignKeyID
     * @return NewsletterDefault
     */
    public function setForeignKeyID($foreignKeyID): self
    {
        $this->foreignKeyID = $foreignKeyID;

        return $this;
    }

    /**
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * @param string $foreignKey
     * @return NewsletterDefault
     */
    public function setForeignKey(string $foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     * @return NewsletterDefault
     */
    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return NewsletterDefault
     */
    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param null $startDate
     * @return NewsletterDefault
     */
    public function setStartDate($startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return null
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param null $startTime
     * @return NewsletterDefault
     */
    public function setStartTime($startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * @return null
     */
    public function getLastStart()
    {
        return $this->lastStart;
    }

    /**
     * @param null $lastStart
     * @return NewsletterDefault
     */
    public function setLastStart($lastStart): self
    {
        $this->lastStart = $lastStart;

        return $this;
    }

    /**
     * @return null
     */
    public function getLastFinish()
    {
        return $this->lastFinish;
    }

    /**
     * @param null $lastFinish
     * @return NewsletterDefault
     */
    public function setLastFinish($lastFinish): self
    {
        $this->lastFinish = $lastFinish;

        return $this;
    }
}
