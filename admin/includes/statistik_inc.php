<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\DB\ReturnType;
use JTL\Linechart;
use JTL\Piechart;
use JTL\Shop;
use JTL\Statistik;

/**
 * @param int    $type
 * @param string $from
 * @param string $to
 * @param int    $intervall
 * @return array|mixed
 */
function gibBackendStatistik(int $type, $from, $to, &$intervall)
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

/**
 * @param int $dayFrom
 * @param int $monthFrom
 * @param int $yearFrom
 * @param int $dayUntil
 * @param int $monthUntil
 * @param int $yearUntil
 * @return bool
 */
function statsDatumPlausi($dayFrom, $monthFrom, $yearFrom, $dayUntil, $monthUntil, $yearUntil)
{
    if ($dayFrom <= 0 || $dayFrom > 31) {
        return false;
    }
    if ($monthFrom <= 0 || $monthFrom > 12) {
        return false;
    }
    if ($yearFrom <= 0) {
        return false;
    }
    if ($dayUntil <= 0 || $dayUntil > 31) {
        return false;
    }
    if ($monthUntil <= 0 || $monthUntil > 12) {
        return false;
    }
    if ($yearUntil <= 0) {
        return false;
    }

    return true;
}

/**
 * @param int $timeSpan
 * @return stdClass
 */
function berechneStatZeitraum($timeSpan)
{
    $res                = new stdClass();
    $res->nDateStampVon = 0;
    $res->nDateStampBis = 0;
    if ((int)$timeSpan > 0) {
        switch ($timeSpan) {
            // Heute
            case 1:
                $res->nDateStampVon = mktime(0, 0, 0, (int)date('n'), (int)date('j'), (int)date('Y'));
                $res->nDateStampBis = mktime(23, 59, 59, (int)date('n'), (int)date('j'), (int)date('Y'));
                break;

            // diese Woche
            case 2:
                $dateData           = ermittleDatumWoche(date('Y') . '-' . date('m') . '-' . date('d'));
                $res->nDateStampVon = $dateData[0];
                $res->nDateStampBis = $dateData[1];
                break;

            // letzte Woche
            case 3:
                $day   = (int)date('d') - 7;
                $month = (int)date('m');
                $year  = (int)date('Y');
                if ($day < 1) {
                    $month--;
                    if ($month < 1) {
                        $month = 12;
                        $year--;
                    }

                    $day = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
                }

                $dateData           = ermittleDatumWoche($year . '-' . $month . '-' . $day);
                $res->nDateStampVon = $dateData[0];
                $res->nDateStampBis = $dateData[1];
                break;

            // diesen Monat
            case 4:
                $res->nDateStampVon = firstDayOfMonth();
                $res->nDateStampBis = lastDayOfMonth();
                break;

            // letzten Monat
            case 5:
                $month = (int)date('m') - 1;
                $year  = (int)date('Y');

                if ($month < 1) {
                    $month = 12;
                    $year--;
                }

                $res->nDateStampVon = firstDayOfMonth($month, $year);
                $res->nDateStampBis = lastDayOfMonth($month, $year);
                break;

            // dieses Jahr
            case 6:
                $res->nDateStampVon = mktime(0, 0, 0, 1, 1, (int)date('Y'));
                $res->nDateStampBis = mktime(23, 59, 59, 12, 31, (int)date('Y'));
                break;

            // letztes Jahr
            case 7:
                $year               = (int)date('Y') - 1;
                $res->nDateStampVon = mktime(0, 0, 0, 1, 1, $year);
                $res->nDateStampBis = mktime(23, 59, 59, 12, 31, $year);
                break;
        }
    }

    return $res;
}

/**
 * @param array $stats
 * @param int   $interval
 * @param int   $type
 * @return string|bool
 */
function getJSON($stats, $interval, $type)
{
    require_once PFAD_ROOT . PFAD_FLASHCHART . 'php-ofc-library/open-flash-chart.php';
    $data = [];
    if (!is_array($stats) || count($stats) === 0) {
        return false;
    }
    if ((int)$interval === 0) {
        return false;
    }
    if (!$type) {
        return false;
    }
    foreach ($stats as $oStat) {
        $data[] = (int)$oStat->nCount;
    }
    // min und max berechnen
    $fMax = round((float)max($data), 2);
    $fMin = round((float)min($data), 2);
    // padding
    $fMin -= $fMin * 0.25;
    $fMax += $fMax * 0.25;
    if ($fMin <= 0) {
        $fMin = 0;
    }
    // abrunden
    $fMin  = floor($fMin);
    $fMax  = floor($fMax);
    $fStep = floor(($fMax - $fMin) / 10);

    switch ($type) {
        // Besucher Stats
        case STATS_ADMIN_TYPE_BESUCHER:
            $colX = 'dZeit';
            // x achse daten
            $xLabels = [];
            foreach ($stats as $oStat) {
                $xLabels[] = (string)$oStat->$colX;
            }

            return setDot($data, $xLabels, null, $fMin, $fMax, $fStep, __('visitor'));
            break;

        // Kundenherkunft
        case STATS_ADMIN_TYPE_KUNDENHERKUNFT:
            $colX = 'cReferer';
            // x achse daten
            $xLabels = [];
            foreach ($stats as $oStat) {
                $xLabels[] = (string)$oStat->$colX;
            }

            return setPie($data, $xLabels);
            break;

        // Suchmaschine
        case STATS_ADMIN_TYPE_SUCHMASCHINE:
            $colX = 'cUserAgent';
            // x achse daten
            $xLabels = [];
            foreach ($stats as $oStat) {
                if (isset($oStat->$colX)) {
                    $xLabels[] = (string)$oStat->$colX;
                } else {
                    $colX      = 'cName';
                    $xLabels[] = (string)$oStat->$colX;
                }
            }

            return setPie($data, $xLabels);
            break;

        // Umsatz
        case STATS_ADMIN_TYPE_UMSATZ:
            $colX = 'dZeit';
            // x achse daten
            $xLabels = [];
            foreach ($stats as $oStat) {
                $xLabels[] = (string)$oStat->$colX;
            }

            $currency = Shop::Container()->getDB()->query(
                "SELECT *
                    FROM twaehrung
                    WHERE cStandard = 'Y'",
                ReturnType::SINGLE_OBJECT
            );

            return setDot($data, $xLabels, null, $fMin, $fMax, $fStep, $currency->cName);
            break;

        // Suchbegriffe
        case STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
            $colX = 'cEinstiegsseite';
            // x achse daten
            $xLabels = [];
            foreach ($stats as $oStat) {
                $xLabels[] = (string)$oStat->$colX;
            }

            return setPie($data, $xLabels);
            break;
    }

    return false;
}

/**
 * @param mixed  $data
 * @param array  $xLabels
 * @param array  $yLabels
 * @param float  $fMin
 * @param float  $fMax
 * @param float  $fStep
 * @param string $cToolTip
 * @return string
 */
function setDot($data, $xLabels, $yLabels, $fMin, $fMax, $fStep, $cToolTip = '')
{
    $d = new solid_dot();
    $d->size(3);
    $d->halo_size(1);
    $d->colour('#0343a3');
    $d->tooltip('#val# ' . $cToolTip);

    $area = new area();
    $area->set_width(2);
    $area->set_default_dot_style($d);
    $area->set_colour('#8cb9fd');
    $area->set_fill_colour('#8cb9fd');
    $area->set_fill_alpha(0.2);
    $area->set_values($data);
    // x achse labels
    $x_labels = new x_axis_labels();
    $x_labels->set_steps(1);
    $x_labels->set_vertical();
    $x_labels->set_colour('#000');
    $x_labels->set_labels($xLabels);
    // x achse
    $x = new x_axis();
    $x->set_colour('#bfbfbf');
    $x->set_grid_colour('#f0f0f0');
    $x->set_labels($x_labels);
    // y achse
    $y = new y_axis();
    $y->set_colour('#bfbfbf');
    $y->set_grid_colour('#f0f0f0');

    $y->set_range($fMin, $fMax, $fStep);
    // chart
    $chart = new open_flash_chart();
    $chart->add_element($area);
    $chart->set_x_axis($x);
    $chart->set_y_axis($y);
    $chart->set_bg_colour('#ffffff');
    $chart->set_number_format(2, true, true, false);

    return $chart->toPrettyString();
}

/**
 * @param array $inputData
 * @param array $xLabels
 * @return string
 */
function setPie($inputData, $xLabels)
{
    $merge = [];
    // Nur max. 10 Werte anzeigen, danach als Sonstiges
    foreach ($inputData as $i => $data) {
        if ($i > 5) {
            $inputData[5] += $data;
        }
        if ($i > 5) {
            unset($inputData[$i]);
        }
    }
    $nValueSonstiges = $inputData[5] ?? null;
    $nPosSonstiges   = 0;
    usort($inputData, 'cmpStat');

    foreach ($inputData as $i => $data) {
        if ($data == $nValueSonstiges) {
            $nPosSonstiges = $i;
            break;
        }
    }
    foreach ($xLabels as $j => $x_labels) {
        if ($j > 5) {
            unset($xLabels[$j]);
        }
    }
    $xLabels[$nPosSonstiges] = __('miscellaneous');
    foreach ($inputData as $i => $data) {
        $cLabel  = $xLabels[$i] . '(' . number_format((float)$data, 0, ',', '.') . ')';
        $merge[] = new pie_value($data, $cLabel);
    }

    $pie = new pie();
    $pie->set_start_angle(35);
    $pie->set_animate(true);
    $pie->set_tooltip('#val# of #total#<br>#percent# of 100%');
    $pie->set_colours(['#1C9E05', '#D4FA00', '#9E1176', '#FF368D', '#454545']);
    $pie->set_values($merge);

    $chart = new open_flash_chart();
    $chart->add_element($pie);
    $chart->set_x_axis(null);
    $chart->set_bg_colour('#ffffff');
    $chart->set_number_format(0, true, true, false);

    return $chart->toPrettyString();
}

/**
 * @param $a
 * @param $b
 * @return int
 */
function cmpStat($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a > $b) ? -1 : 1;
}

/**
 * @param int $type
 * @return mixed
 */
function gibMappingDaten($type)
{
    if (!$type) {
        return [];
    }

    $mapping                                   = [];
    $mapping[STATS_ADMIN_TYPE_BESUCHER]        = [
        'nCount' => __('count'),
        'dZeit'  => __('date')
    ];
    $mapping[STATS_ADMIN_TYPE_KUNDENHERKUNFT]  = [
        'nCount'   => __('count'),
        'dZeit'    => __('date'),
        'cReferer' => __('origin')
    ];
    $mapping[STATS_ADMIN_TYPE_SUCHMASCHINE]    = [
        'nCount'     => __('count'),
        'dZeit'      => __('date'),
        'cUserAgent' => __('userAgent')
    ];
    $mapping[STATS_ADMIN_TYPE_UMSATZ]          = [
        'nCount' => __('amount'),
        'dZeit'  => __('date')
    ];
    $mapping[STATS_ADMIN_TYPE_EINSTIEGSSEITEN] = [
        'nCount'          => __('count'),
        'dZeit'           => __('date'),
        'cEinstiegsseite' => __('entryPage')
    ];

    return $mapping[$type];
}

/**
 * @param int $type
 * @return string
 */
function GetTypeNameStats($type)
{
    $names = [
        1 => __('visitor'),
        2 => __('customerHeritage'),
        3 => __('searchEngines'),
        4 => __('sales'),
        5 => __('entryPages')
    ];

    return $names[$type] ?? '';
}

/**
 * @param int $type
 * @return stdClass
 */
function getAxisNames($type)
{
    $axis    = new stdClass();
    $axis->y = 'nCount';
    switch ($type) {
        case STATS_ADMIN_TYPE_BESUCHER:
            $axis->x = 'dZeit';
            break;
        case STATS_ADMIN_TYPE_KUNDENHERKUNFT:
            $axis->x = 'cReferer';
            break;
        case STATS_ADMIN_TYPE_SUCHMASCHINE:
            $axis->x = 'cUserAgent';
            break;
        case STATS_ADMIN_TYPE_UMSATZ:
            $axis->x = 'dZeit';
            break;
        case STATS_ADMIN_TYPE_EINSTIEGSSEITEN:
            $axis->x = 'cEinstiegsseite';
            break;
    }

    return $axis;
}

/**
 * @param array $members
 * @param array $mapping
 * @return array
 */
function mappeDatenMember($members, $mapping)
{
    if (is_array($members) && count($members) > 0) {
        foreach ($members as $i => $data) {
            foreach ($data as $j => $member) {
                $members[$i][$j]    = [];
                $members[$i][$j][0] = $member;
                $members[$i][$j][1] = $mapping[$member];
            }
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
 */
function prepareLineChartStats($stats, $name, $axis, $mod = 1)
{
    $chart = new Linechart(['active' => false]);

    if (is_array($stats) && count($stats) > 0) {
        $chart->setActive(true);
        $data = [];
        $y    = $axis->y;
        $x    = $axis->x;
        foreach ($stats as $j => $stat) {
            $obj    = new stdClass();
            $obj->y = round((float)$stat->$y, 2, 1);

            if ($j % $mod === 0) {
                $chart->addAxis($stat->$x);
            } else {
                $chart->addAxis('|');
            }

            $data[] = $obj;
        }

        $chart->addSerie($name, $data);
        $chart->memberToJSON();
    }

    return $chart;
}

/**
 * @param array  $stats
 * @param string $name
 * @param object $axis
 * @param int    $maxEntries
 * @return Piechart
 */
function preparePieChartStats($stats, $name, $axis, $maxEntries = 6)
{
    $chart = new Piechart(['active' => false]);
    if (is_array($stats) && count($stats) > 0) {
        $chart->setActive(true);
        $data = [];

        $y = $axis->y;
        $x = $axis->x;

        // Zeige nur $maxEntries Main Member + 1 Sonstige an, sonst wird es zu unuebersichtlich
        if (count($stats) > $maxEntries) {
            $statstmp  = [];
            $other     = new stdClass();
            $other->$y = 0;
            $other->$x = __('miscellaneous');
            foreach ($stats as $i => $stat) {
                if ($i < $maxEntries) {
                    $statstmp[] = $stat;
                } else {
                    $other->$y += $stat->$y;
                }
            }

            $statstmp[] = $other;
            $stats      = $statstmp;
        }

        foreach ($stats as $stat) {
            $value  = round((float)$stat->$y, 2, 1);
            $data[] = [$stat->$x, $value];
        }

        $chart->addSerie($name, $data);
        $chart->memberToJSON();
    }

    return $chart;
}

/**
 * @param array  $series
 * @param object $axis
 * @param int    $mod
 * @return Linechart
 */
function prepareLineChartStatsMulti($series, $axis, $mod = 1)
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
 */
function GetLineChartColors($number)
{
    $colors = [
        ['#435a6b', '#a168f2', '#435a6b'],
        ['#5cbcf6', '#5cbcf6', '#5cbcf6']
    ];

    return $colors[$number] ?? $colors[0];
}
