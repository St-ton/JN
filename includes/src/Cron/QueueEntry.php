<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron;


class QueueEntry
{

    public $kJobQueue;

    public $kCron;

    public $kKey;

    public $nLimitN;

    public $nLimitM;

    public $nLastArticleID;

    public $nInArbeit;

    public $cJobArt;

    public $cTabelle;

    public $cKey;

    public $dStartZeit;

    public $dZuletztGelaufen;

    public function __construct(JobInterface $job) {
        $this->kCron            = $job->getID();
        $this->kKey             = $job->getForeignKeyID();
        $this->nLimitN          = $job->getExecuted();
        $this->nLimitM          = $job->getLimit();
        $this->nLastArticleID   = 0;

        $this->nInArbeit        = 0;

        $this->cJobArt          = $job->getType();
        $this->cTabelle         = $job->getTable();
        $this->cKey             = $job->getForeignKey();
        $this->dStartZeit       = $job->getDateLastStarted();
        $this->dZuletztGelaufen = new \DateTime();
    }
}
