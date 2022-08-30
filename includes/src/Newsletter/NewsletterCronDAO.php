<?php declare(strict_types=1);

namespace JTL\Newsletter;

use DateTime;
use JTL\Shop;
use stdClass;

/**
 * Class NewsletterCronDAO
 * reflects all columns of the table `tcron`, except the auto_increment column
 * @package JTL\Newsletter
 */
class NewsletterCronDAO
{
    /**
     * @var int
     */
    private int $foreignKeyID = 0;

    /**
     * @var string
     */
    private string $foreignKey = 'kNewsletter';

    /**
     * @var string
     */
    private string $tableName = 'tnewsletter';

    /**
     * @var string
     */
    private string $name = 'Newsletter';

    /**
     * @var string
     */
    private string $jobType = 'newsletter';

    /**
     * @var int
     */
    private int $frequency;

    /**
     * @var string
     */
    private string $startDate;

    /**
     * @var string
     */
    private string $startTime;

    /**
     * @var string
     */
    private string $lastStart = '_DBNULL_';

    /**
     * @var string
     */
    private string $lastFinish = '_DBNULL_';

    /**
     * NewsletterCronDAO constructor.
     * pre-define all table columns here, for inserting or updating them later
     * @throws \Exception
     */
    public function __construct()
    {
        $this->startDate = (new DateTime())->format('Y-m-d H:i:s');
        $this->startTime = (new DateTime())->format('H:i:s');
        $this->frequency = Shop::getSettingValue(\CONF_NEWSLETTER, 'newsletter_send_delay');
    }

    /**
     * @return int
     */
    public function getForeignKeyID(): int
    {
        return $this->foreignKeyID;
    }

    /**
     * @param int $foreignKeyID
     * @return NewsletterCronDAO
     */
    public function setForeignKeyID(int $foreignKeyID): self
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
     * @return NewsletterCronDAO
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
     * @return NewsletterCronDAO
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
     * @return NewsletterCronDAO
     */
    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     * @return NewsletterCronDAO
     */
    public function setStartDate(string $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->startTime;
    }

    /**
     * @param string $startTime
     * @return NewsletterCronDAO
     */
    public function setStartTime(string $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastStart(): string
    {
        return $this->lastStart;
    }

    /**
     * @param string $lastStart
     * @return NewsletterCronDAO
     */
    public function setLastStart(string $lastStart): self
    {
        $this->lastStart = $lastStart;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastFinish(): string
    {
        return $this->lastFinish;
    }

    /**
     * @param string $lastFinish
     * @return NewsletterCronDAO
     */
    public function setLastFinish(string $lastFinish): self
    {
        $this->lastFinish = $lastFinish;

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getData(): stdClass
    {
        $res = new stdClass();
        foreach (\get_object_vars($this) as $k => $v) {
            $res->$k = $v;
        }

        return $res;
    }
}
