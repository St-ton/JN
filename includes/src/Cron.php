<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Cron
 */
class Cron
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
        return ($this->kKey > 0 && strlen($this->cTabelle) > 0)
            ? Shop::Container()->getDB()->selectAll($this->cTabelle, $this->cKey, (int)$this->kKey)
            : false;
    }

    /**
     * @return int|bool
     */
    public function speicherInDB()
    {
        return $this->kKey > 0 && $this->cKey && $this->cTabelle && $this->cName && $this->nAlleXStd && $this->dStart
            ? Shop::Container()->getDB()->insert('tcron', $this)
            : false;
    }

    /**
     * @param string $cJobArt
     * @param string $dStart
     * @param int    $nLimitM
     * @return int|bool
     */
    public function speicherInJobQueue($cJobArt, $dStart, $nLimitM)
    {
        if ($dStart && $nLimitM > 0 && strlen($cJobArt) > 0) {
            $oJobQueue             = new stdClass();
            $oJobQueue->kCron      = $this->kCron;
            $oJobQueue->kKey       = $this->kKey;
            $oJobQueue->cKey       = $this->cKey;
            $oJobQueue->cTabelle   = $this->cTabelle;
            $oJobQueue->cJobArt    = $cJobArt;
            $oJobQueue->dStartZeit = $dStart;
            $oJobQueue->nLimitN    = 0;
            $oJobQueue->nLimitM    = $nLimitM;
            $oJobQueue->nInArbeit  = 0;

            return Shop::Container()->getDB()->insert('tjobqueue', $oJobQueue);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function updateCronDB(): bool
    {
        if ($this->kCron > 0) {
            $_upd                = new stdClass();
            $_upd->kKey          = (int)$this->kKey;
            $_upd->cKey          = $this->cKey;
            $_upd->cTabelle      = $this->cTabelle;
            $_upd->cName         = $this->cName;
            $_upd->cJobArt       = $this->cJobArt;
            $_upd->nAlleXStd     = (int)$this->nAlleXStd;
            $_upd->dStart        = $this->dStart;
            $_upd->dLetzterStart = $this->dLetzterStart;

            return Shop::Container()->getDB()->update('tcron', 'kCron', $this->kCron, $_upd) >= 0;
        }

        return false;
    }
}
