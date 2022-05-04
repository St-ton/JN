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
        $where    = '';
        $having   = '';
        if ($maxDays > 0) {
            $params['maxDays'] = $maxDays;
            $where             = 'AND tbestellung.dErstellt > SUBDATE(CURDATE(), :maxDays)';
        }
        if ($minCount > 0) {
            $params['minCount'] = $minCount;
            $having             = 'HAVING SUM(twarenkorbpos.nAnzahl) > :minCount';
        }

        $this->db->query('TRUNCATE tbestseller');
        $this->db->queryPrepared(
            'INSERT INTO tbestseller (kArtikel, fAnzahl)
                SELECT twarenkorbpos.kArtikel, SUM(twarenkorbpos.nAnzahl) AS anz
                    FROM tbestellung
                    INNER JOIN twarenkorbpos ON twarenkorbpos.kWarenkorb = tbestellung.kWarenkorb
                    WHERE tbestellung.cStatus > 1
                        AND twarenkorbpos.kArtikel > 0
                        ' . $where . '
                    GROUP BY twarenkorbpos.kArtikel
                    ' . $having,
            $params
        );
        $this->setFinished(true);

        return $this;
    }
}
