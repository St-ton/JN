<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class JobQueue
 */
class JobQueue
{
    /**
     * @var int
     */
    public $kJobQueue;

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
    public $nLimitN;

    /**
     * @var int
     */
    public $nLimitM;

    /**
     * @var int
     */
    public $nInArbeit;

    /**
     * @var string
     */
    public $cJobArt;

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
    public $dStartZeit;

    /**
     * @var string
     */
    public $dZuletztGelaufen;

    /**
     * @param int|null $kJobQueue
     * @param int      $kCron
     * @param int      $kKey
     * @param int      $nLimitN
     * @param int      $nLimitM
     * @param int      $nInArbeit
     * @param string   $cJobArt
     * @param string   $cTabelle
     * @param string   $cKey
     * @param string   $dStartZeit
     * @param string   $dZuletztGelaufen
     */
    public function __construct($kJobQueue = null, $kCron = 0, $kKey = 0, $nLimitN = 0, $nLimitM = 0, $nInArbeit = 0, $cJobArt = '', $cTabelle = '', $cKey = '', $dStartZeit = 'now()', $dZuletztGelaufen = '0000-00-00')
    {
        $this->kJobQueue        = $kJobQueue;
        $this->kCron            = $kCron;
        $this->kKey             = $kKey;
        $this->nLimitN          = $nLimitN;
        $this->nLimitM          = $nLimitM;
        $this->nInArbeit        = $nInArbeit;
        $this->cJobArt          = $cJobArt;
        $this->cTabelle         = $cTabelle;
        $this->cKey             = $cKey;
        $this->dStartZeit       = $dStartZeit;
        $this->dZuletztGelaufen = $dZuletztGelaufen;
    }

    /**
     * @return mixed
     */
    public function holeJobArt()
    {
        if ($this->kKey > 0 && strlen($this->cTabelle) > 0) {
            return Shop::DB()->select(Shop::DB()->escape($this->cTabelle), Shop::DB()->escape($this->cKey), (int)$this->kKey);
        }

        return;
    }

    /**
     * @return int
     */
    public function speicherJobInDB()
    {
        if ($this->kKey > 0 && strlen($this->cJobArt) > 0 && strlen($this->cKey) > 0 && strlen($this->cTabelle) > 0 && $this->nLimitM > 0 && strlen($this->dStartZeit) > 0) {
            $queue = kopiereMembers($this);
            unset($queue->kJobQueue);

            return Shop::DB()->insert('tjobqueue', $queue);
        }

        return 0;
    }

    /**
     * @return int
     */
    public function updateJobInDB()
    {
        if ($this->kJobQueue > 0) {
            $_upd                   = new stdClass();
            $_upd->kCron            = (int)$this->kCron;
            $_upd->kKey             = (int)$this->kKey;
            $_upd->nLimitN          = (int)$this->nLimitN;
            $_upd->nLimitM          = (int)$this->nLimitM;
            $_upd->nInArbeit        = (int)$this->nInArbeit;
            $_upd->cJobArt          = $this->cJobArt;
            $_upd->cTabelle         = $this->cTabelle;
            $_upd->cKey             = $this->cKey;
            $_upd->dStartZeit       = $this->dStartZeit;
            $_upd->dZuletztGelaufen = $this->dZuletztGelaufen;

            return Shop::DB()->update('tjobqueue', 'kJobQueue', (int)$this->kJobQueue, $_upd);
        }

        return 0;
    }

    /**
     * @return int
     */
    public function deleteJobInDB()
    {
        return ($this->kJobQueue > 0) ?
            Shop::DB()->delete('tjobqueue', 'kJobQueue', (int)$this->kJobQueue) :
            0;
    }
}
