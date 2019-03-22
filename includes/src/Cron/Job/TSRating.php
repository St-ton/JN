<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\TrustedShops;

/**
 * Class TSRating
 * @package JTL\Cron\Job
 */
final class TSRating extends Job
{
    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $validLanguageCodes = ['de', 'en', 'fr', 'pl', 'es'];
        foreach ($validLanguageCodes as $languageCode) {
            $ts     = new TrustedShops(-1, $languageCode);
            $rating = $ts->holeKundenbewertungsstatus($languageCode);
            if ((int)$rating->nStatus === 1 && \mb_strlen($rating->cTSID) > 0) {
                $res = $ts->aenderKundenbewertungsstatus(
                    $rating->cTSID,
                    1,
                    $languageCode
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
