<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Jobs;


use Cron\Job;
use Cron\JobInterface;
use Cron\QueueEntry;

/**
 * Class TSRating
 * @package Cron\Jobs
 */
class TSRating extends Job
{
    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $cValidSprachISO_arr = ['de', 'en', 'fr', 'pl', 'es'];
        foreach ($cValidSprachISO_arr as $cValidSprachISO) {
            $ts     = new \TrustedShops(-1, $cValidSprachISO);
            $rating = $ts->holeKundenbewertungsstatus($cValidSprachISO);
            if ((int)$rating->nStatus === 1 && strlen($rating->cTSID) > 0) {
                $res = $ts->aenderKundenbewertungsstatus(
                    $rating->cTSID,
                    1,
                    $cValidSprachISO
                );
                if ($res !== 1) {
                    $ts->aenderKundenbewertungsstatusDB(
                        0,
                        $rating->cISOSprache
                    );
                }
            }
        }
        $this->setFinished(true);

        return $this;
    }
}
