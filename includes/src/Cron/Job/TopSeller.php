<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Shop;

/**
 * Class TopSeller
 * @package JTL\Cron\Job
 */
final class TopSeller extends Job
{
    /**
     * @inheritDoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);

        $maxDays  = Shop::getSettingValue(\CONF_GLOBAL, 'global_bestseller_tage') ?? 90;
        $minCount = Shop::getSettingValue(\CONF_GLOBAL, 'global_bestseller_minanzahl') ?? 10;
        $params   = [];
        if ($maxDays > 0) {
            $params['maxDays'] = $maxDays;
        }
        if ($minCount > 0) {
            $params['minCount'] = $minCount;
        }

        $this->db->query('TRUNCATE tbestseller');
        $this->db->queryPrepared(
            'INSERT INTO tbestseller (kArtikel, fAnzahl, isBestseller)
                SELECT m.kArtikel, SUM(m.nAnzahl) AS anz,
                       SUM(m.nAnzahl) >= :minCount AND m.dErstellt > SUBDATE(CURDATE(), :maxDays)
                    FROM (
                        SELECT twarenkorbpos.kArtikel, twarenkorbpos.nAnzahl, tbestellung.dErstellt
                        FROM tbestellung
                        INNER JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                        WHERE tbestellung.cStatus > 1
                          AND twarenkorbpos.kArtikel > 0
                        UNION ALL
                        SELECT tartikel.kVaterArtikel, twarenkorbpos.nAnzahl, tbestellung.dErstellt
                        FROM tbestellung
                        INNER JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                        INNER JOIN tartikel ON twarenkorbpos.kArtikel = tartikel.kArtikel
                        WHERE tbestellung.cStatus > 1
                          AND twarenkorbpos.kArtikel > 0
                          AND tartikel.kVaterArtikel > 0
                    ) AS m
                    GROUP BY m.kArtikel',
            $params
        );
        $this->setFinished(true);

        return $this;
    }
}
