<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;

/**
 * Class QueueEntry
 * @package Cron
 */
class QueueEntry
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
    public $nLastArticleID;

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
     * @var \DateTime
     */
    public $dStartZeit;

    /**
     * @var \DateTime
     */
    public $dZuletztGelaufen;

    /**
     * QueueEntry constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->kJobQueue        = (int)$data->kJobQueue;
        $this->kCron            = (int)$data->kCron;
        $this->kKey             = (int)$data->kKey;
        $this->nLimitN          = (int)$data->nLimitN;
        $this->nLimitM          = (int)$data->nLimitM;
        $this->nLastArticleID   = (int)$data->nLastArticleID;
        $this->nInArbeit        = 0;
        $this->cJobArt          = $data->cJobArt;
        $this->cTabelle         = $data->cTabelle;
        $this->cKey             = $data->cKey;
        $this->dStartZeit       = new \DateTime($data->dStartZeit);
        $this->dZuletztGelaufen = new \DateTime($data->dZuletztGelaufen);
    }
}
