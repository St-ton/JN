<?php

use JTL\Linechart;
use JTL\Piechart;
use JTL\Statistik;

/**
 * @param int $type
 * @return array
 * @deprecated since 5.2.0
 */
function gibMappingDaten(int $type): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int $type
 * @return string
 * @deprecated since 5.2.0
 */
function GetTypeNameStats($type): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param array $members
 * @param array $mapping
 * @return array
 * @deprecated since 5.2.0
 */
function mappeDatenMember(array $members, array $mapping): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    foreach ($members as $i => $data) {
        foreach ($data as $j => $member) {
            $members[$i][$j]    = [];
            $members[$i][$j][0] = $member;
            $members[$i][$j][1] = $mapping[$member];
        }
    }

    return $members;
}

/**
 * @param array  $stats
 * @param string $name
 * @param object $axis
 * @param int    $mod
 * @return Linechart
 * @deprecated since 5.2.0
 */
function prepareLineChartStats($stats, $name, $axis, $mod = 1): Linechart
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new Linechart(['active' => false]);
}

/**
 * @param array  $stats
 * @param string $name
 * @param object $axis
 * @param int    $maxEntries
 * @return Piechart
 * @deprecated since 5.2.0
 */
function preparePieChartStats($stats, $name, $axis, $maxEntries = 6): Piechart
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new Piechart(['active' => false]);
}

/**
 * @param int $type
 * @return stdClass
 * @todo!
 */
function getAxisNames($type): stdClass
{
    $axis    = new stdClass();
    $axis->y = 'nCount';
    switch ($type) {
        case STATS_ADMIN_TYPE_UMSATZ:
        case STATS_ADMIN_TYPE_BESUCHER:
            $axis->x = 'dZeit';
            break;
        case STATS_ADMIN_TYPE_KUNDENHERKUNFT:
            $axis->x = 'cReferer';
            break;
        case STATS_ADMIN_TYPE_SUCHMASCHINE:
            $axis->x = 'cUserAgent';
            break;
        case STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
            $axis->x = 'cEinstiegsseite';
            break;
    }

    return $axis;
}

/**
 * @param array  $series
 * @param object $axis
 * @param int    $mod
 * @return Linechart
 * @todo!
 */
function prepareLineChartStatsMulti($series, $axis, $mod = 1): Linechart
{
    $chart = new Linechart(['active' => false]);
    if (is_array($series) && count($series) > 0) {
        $i = 0;
        foreach ($series as $Name => $Serie) {
            if (is_array($Serie) && count($Serie) > 0) {
                $chart->setActive(true);
                $data = [];
                $y    = $axis->y;
                $x    = $axis->x;
                foreach ($Serie as $j => $stat) {
                    $obj    = new stdClass();
                    $obj->y = round((float)$stat->$y, 2, 1);

                    if ($j % $mod === 0) {
                        $chart->addAxis($stat->$x);
                    } else {
                        $chart->addAxis('|');
                    }

                    $data[] = $obj;
                }

                $colors = GetLineChartColors($i);
                $chart->addSerie($Name, $data, $colors[0], $colors[1], $colors[2]);
                $chart->memberToJSON();
            }

            $i++;
        }
    }

    return $chart;
}

/**
 * @param int $number
 * @return mixed
 * @todo!
 */
function GetLineChartColors($number)
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
 * @todo!
 */
function gibBackendStatistik(int $type, int $from, int $to, &$intervall): array
{
    $data = [];
    if ($type > 0 && $from > 0 && $to > 0) {
        $stats     = new Statistik($from, $to);
        $intervall = $stats->getAnzeigeIntervall();
        switch ($type) {
            case STATS_ADMIN_TYPE_BESUCHER:
                $data = $stats->holeBesucherStats();
                break;
            case STATS_ADMIN_TYPE_KUNDENHERKUNFT:
                $data = $stats->holeKundenherkunftStats();
                break;
            case STATS_ADMIN_TYPE_SUCHMASCHINE:
                $data = $stats->holeBotStats();
                break;
            case STATS_ADMIN_TYPE_UMSATZ:
                $data = $stats->holeUmsatzStats();
                break;
            case STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
                $data = $stats->holeEinstiegsseiten();
                break;
        }
    }

    return $data;
}
