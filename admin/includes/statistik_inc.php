<?php declare(strict_types=1);

use JTL\Backend\Stats;
use JTL\Linechart;
use JTL\Piechart;

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
 * @deprecated since 5.2.0
 */
function getAxisNames($type): stdClass
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Backend\Stats::getAxisNames() instead.',
        E_USER_DEPRECATED
    );
    return Stats::getAxisNames((int)$type);
}

/**
 * @param array  $series
 * @param object $axis
 * @param int    $mod
 * @return Linechart
 * @deprecated since 5.2.0
 */
function prepareLineChartStatsMulti($series, $axis, $mod = 1): Linechart
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Backend\Stats::prepareLineChartStatsMulti() instead.',
        E_USER_DEPRECATED
    );
    return Stats::prepareLineChartStatsMulti($series, $axis, $mod);
}

/**
 * @param int $number
 * @return array
 * @deprecated since 5.2.0
 */
function GetLineChartColors($number)
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Backend\Stats::getLineChartColors() instead.',
        E_USER_DEPRECATED
    );
    return Stats::getLineChartColors($number);
}

/**
 * @param int $type
 * @param int $from
 * @param int $to
 * @param int $intervall
 * @return array
 * @deprecated since 5.2.0
 */
function gibBackendStatistik(int $type, int $from, int $to, &$intervall): array
{
    trigger_error(
        __FUNCTION__ . ' is deprecated. Use JTL\Backend\Stats::getBackendStats() instead.',
        E_USER_DEPRECATED
    );
    return Stats::getBackendStats($type, $from, $to, $intervall);
}
