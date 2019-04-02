<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron;

use JTL\Shop;
use stdClass;

/**
 * Class LegacyCron
 * @package JTL\Cron
 * @todo: finalize refactoring and remove this class
 */
class LegacyCron
{
    /**
     * @var int
     */
    public $kCron;

    /**
     * @var int
     */
    public $kKey;

    /**
     * @var int
     */
    public $nAlleXStd;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cTabelle;

    /**
     * @var string
     */
    public $cKey;

    /**
     * @var string
     */
    public $cJobArt;

    /**
     * @var string
     */
    public $dStart;

    /**
     * @var string
     */
    public $dStartZeit;

    /**
     * @var string
     */
    public $dLetzterStart;

    /**
     * @param int    $kCron
     * @param int    $kKey
     * @param int    $nAlleXStd
     * @param string $cName
     * @param string $cJobArt
     * @param string $cTabelle
     * @param string $cKey
     * @param string $dStart
     * @param string $dStartZeit
     * @param string $dLetzterStart
     */
    public function __construct(
        int $kCron = 0,
        int $kKey = 0,
        int $nAlleXStd = 0,
        string $cName = '',
        string $cJobArt = '',
        string $cTabelle = '',
        string $cKey = '',
        string $dStart = null,
        string $dStartZeit = null,
        string $dLetzterStart = null
    ) {
        $this->kCron         = $kCron;
        $this->kKey          = $kKey;
        $this->cKey          = $cKey;
        $this->cTabelle      = $cTabelle;
        $this->cName         = $cName;
        $this->cJobArt       = $cJobArt;
        $this->nAlleXStd     = $nAlleXStd;
        $this->dStart        = $dStart;
        $this->dStartZeit    = $dStartZeit;
        $this->dLetzterStart = $dLetzterStart;
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

            return Shop::Container()->getDB()->insert('tcron', $ins);
        }

        return false;
    }

    /**
     * @param string $cJobArt
     * @param string $dStart
     * @param int    $nLimitM
     * @return int|bool
     */
    public function speicherInJobQueue($cJobArt, $dStart, $nLimitM)
    {
        if ($dStart && $nLimitM > 0 && \mb_strlen($cJobArt) > 0) {
            $ins                = new stdClass();
            $ins->cronID        = $this->kCron;
            $ins->foreignKeyID  = $this->kKey;
            $ins->foreignKey    = $this->cKey;
            $ins->tableName     = $this->cTabelle;
            $ins->jobType       = $cJobArt;
            $ins->startTime     = $dStart;
            $ins->tasksExecuted = 0;
            $ins->taskLimit     = $nLimitM;
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
