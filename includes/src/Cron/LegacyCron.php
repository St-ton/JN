<?php

namespace JTL\Cron;

use JTL\Shop;
use stdClass;

/**
 * Class LegacyCron
 * @package JTL\Cron
 * @deprecated since 5.2.0
 */
class LegacyCron
{
    /**
     * @param int         $kCron
     * @param int         $kKey
     * @param int         $nAlleXStd
     * @param string      $cName
     * @param string      $cJobArt
     * @param string      $cTabelle
     * @param string      $cKey
     * @param string|null $dStart
     * @param string|null $dStartZeit
     * @param string|null $dLetzterStart
     */
    public function __construct(
        public int     $kCron = 0,
        public int     $kKey = 0,
        public int     $nAlleXStd = 0,
        public string  $cName = '',
        public string  $cJobArt = '',
        public string  $cTabelle = '',
        public string  $cKey = '',
        public ?string $dStart = null,
        public ?string $dStartZeit = null,
        public ?string $dLetzterStart = null
    ) {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @return array|bool
     */
    public function holeCronArt()
    {
        return ($this->kKey > 0 && \mb_strlen($this->cTabelle) > 0)
            ? Shop::Container()->getDB()->selectAll($this->cTabelle, $this->cKey, (int)$this->kKey)
            : false;
    }

    /**
     * @return int|bool
     */
    public function speicherInDB()
    {
        if ($this->kKey > 0 && $this->cKey && $this->cTabelle && $this->cName && $this->nAlleXStd && $this->dStart) {
            $ins               = new stdClass();
            $ins->foreignKeyID = $this->kKey;
            $ins->foreignKey   = $this->cKey;
            $ins->tableName    = $this->cTabelle;
            $ins->name         = $this->cName;
            $ins->jobType      = $this->cJobArt;
            $ins->frequency    = $this->nAlleXStd;
            $ins->startDate    = $this->dStart;
            $ins->startTime    = $this->dStartZeit;
            $ins->lastStart    = $this->dLetzterStart ?? '_DBNULL_';
            $ins->nextStart    = $this->dStart;

            return Shop::Container()->getDB()->insert('tcron', $ins);
        }

        return false;
    }

    /**
     * @param string $jobType
     * @param string $startTime
     * @param int    $lastLimit - "nLimitM"
     * @return int|bool
     */
    public function speicherInJobQueue(string $jobType, string $startTime, $lastLimit)
    {
        if ($startTime && $lastLimit > 0 && \mb_strlen($jobType) > 0) {
            $ins                = new stdClass();
            $ins->cronID        = $this->kCron;
            $ins->foreignKeyID  = $this->kKey;
            $ins->foreignKey    = $this->cKey;
            $ins->tableName     = $this->cTabelle;
            $ins->jobType       = $jobType;
            $ins->startTime     = $startTime;
            $ins->tasksExecuted = 0;
            $ins->taskLimit     = $lastLimit;
            $ins->isRunning     = 0;

            return Shop::Container()->getDB()->insert('tjobqueue', $ins);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function updateCronDB(): bool
    {
        if ($this->kCron > 0) {
            $upd               = new stdClass();
            $upd->foreignKeyID = (int)$this->kKey;
            $upd->foreignKey   = $this->cKey;
            $upd->tableName    = $this->cTabelle;
            $upd->name         = $this->cName;
            $upd->jobType      = $this->cJobArt;
            $upd->frequency    = (int)$this->nAlleXStd;
            $upd->startDate    = $this->dStart;
            $upd->lastStart    = $this->dLetzterStart ?? '_DBNULL';

            return Shop::Container()->getDB()->update('tcron', 'cronID', $this->kCron, $upd) >= 0;
        }

        return false;
    }
}
