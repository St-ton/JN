<?php declare(strict_types=1);

namespace JTL\Backend;

use JTL\Linechart;
use JTL\Statistik;
use stdClass;

/**
 * Class Stats
 * @package JTL\Backend
 */
class Stats
{
    /**
     * @param int $number
     * @return string[]
     * @former GetLineChartColors()
     * @since 5.2.0
     */
    public static function getLineChartColors(int $number): array
    {
        $colors = [
            ['#435a6b', '#a168f2', '#435a6b'],
            ['#5cbcf6', '#5cbcf6', '#5cbcf6']
        ];

        return $colors[$number] ?? $colors[0];
    }


    /**
     * @param int $type
     * @param int $from
     * @param int $to
     * @param int $intervall
     * @return array
     * @former gibBackendStatistik()
     */
    public static function getBackendStats(int $type, int $from, int $to, int &$intervall): array
    {
        $data = [];
        if ($type > 0 && $from > 0 && $to > 0) {
            $stats     = new Statistik($from, $to);
            $intervall = $stats->getAnzeigeIntervall();
            $data      = match ($type) {
                STATS_ADMIN_TYPE_BESUCHER        => $stats->holeBesucherStats(),
                STATS_ADMIN_TYPE_KUNDENHERKUNFT  => $stats->holeKundenherkunftStats(),
                STATS_ADMIN_TYPE_SUCHMASCHINE    => $stats->holeBotStats(),
                STATS_ADMIN_TYPE_UMSATZ          => $stats->holeUmsatzStats(),
                STATS_ADMIN_TYPE_EINSTIEGSSEITEN => $stats->holeEinstiegsseiten(),
                default                          => [],
            };
        }

        return $data;
    }

    /**
     * @param int $type
     * @return stdClass
     * @former getAxisNames()
     * @since 5.2.0
     */
    public static function getAxisNames(int $type): stdClass
    {
        $axis    = new stdClass();
        $axis->y = 'nCount';
        $axis->x = match ($type) {
            STATS_ADMIN_TYPE_UMSATZ, STATS_ADMIN_TYPE_BESUCHER => 'dZeit',
            STATS_ADMIN_TYPE_KUNDENHERKUNFT                    => 'cReferer',
            STATS_ADMIN_TYPE_SUCHMASCHINE                      => 'cUserAgent',
            STATS_ADMIN_TYPE_EINSTIEGSSEITEN                   => 'cEinstiegsseite',
        };

        return $axis;
    }


    /**
     * @param array  $series
     * @param object $axis
     * @param int    $mod
     * @return Linechart
     * @former prepareLineChartStatsMulti()
     * @since 5.2.0
     */
    public static function prepareLineChartStatsMulti(array $series, object $axis, int $mod = 1): Linechart
    {
        $chart = new Linechart(['active' => false]);
        if (\count($series) === 0) {
            return $chart;
        }
        $i = 0;
        foreach ($series as $name => $seriesData) {
            if (\is_array($seriesData) && \count($seriesData) > 0) {
                $chart->setActive(true);
                $data = [];
                $y    = $axis->y;
                $x    = $axis->x;
                foreach ($seriesData as $j => $stat) {
                    $obj    = new stdClass();
                    $obj->y = \round((float)$stat->$y, 2, 1);
                    if ($j % $mod === 0) {
                        $chart->addAxis($stat->$x);
                    } else {
                        $chart->addAxis('|');
                    }

                    $data[] = $obj;
                }
                $colors = self::getLineChartColors($i);
                $chart->addSerie($name, $data, $colors[0], $colors[1], $colors[2]);
                $chart->memberToJSON();
            }
            $i++;
        }

        return $chart;
    }
}
