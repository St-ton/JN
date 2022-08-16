<?php

use JTL\Campaign;
use JTL\Linechart;
use JTL\Shop;
use function Functional\reindex;

/**
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function holeAlleKampagnenDefinitionen(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return reindex(
        Shop::Container()->getDB()->getObjects(
            'SELECT *
                FROM tkampagnedef
                ORDER BY kKampagneDef'
        ),
        static function ($e) {
            return (int)$e->kKampagneDef;
        }
    );
}

/**
 * @param int $definitionID
 * @return stdClass|null
 * @deprecated since 5.2.0
 */
function holeKampagneDef(int $definitionID): ?stdClass
{
    return Shop::Container()->getDB()->select('tkampagnedef', 'kKampagneDef', $definitionID);
}

/**
 * @param array $campaigns
 * @param array $definitions
 * @return array
 * @deprecated since 5.2.0
 */
function holeKampagneGesamtStats(array $campaigns, array $definitions): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int $a
 * @param int $b
 * @return int
 * @deprecated since 5.2.0
 */
function kampagneSortDESC($a, $b): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @param int $a
 * @param int $b
 * @return int
 * @deprecated since 5.2.0
 */
function kampagneSortASC($a, $b): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @param int   $campaignID
 * @param array $definitions
 * @return array
 * @deprecated since 5.2.0
 */
function holeKampagneDetailStats(int $campaignID, array $definitions): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int    $campaignID
 * @param object $definition
 * @param string $cStamp
 * @param string $text
 * @param array  $members
 * @param string $sql
 * @return array
 * @deprecated since 5.2.0
 */
function holeKampagneDefDetailStats(int $campaignID, $definition, $cStamp, &$text, &$members, $sql): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $select
 * @param string $where
 * @param string $stamp
 * @deprecated since 5.2.0
 */
function baueDefDetailSELECTWHERE(&$select, &$where, $stamp)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}

/**
 * @return array
 * @deprecated since 5.2.0
 */
function gibDetailDatumZeitraum(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $oldStamp
 * @param int    $direction - -1 = Vergangenheit, 1 = Zukunft
 * @param int    $view
 * @return string
 * @deprecated since 5.2.0
 */
function gibStamp($oldStamp, int $direction, int $view): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return $oldStamp;
}

/**
 * @param Campaign $campaign
 * @return int
 * @deprecated since 5.2.0
 */
function speicherKampagne($campaign): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 2;
}

/**
 * @param int $code
 * @return string
 * @deprecated since 5.2.0
 */
function mappeFehlerCodeSpeichern(int $code): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return __('errorCampaignSave');
}

/**
 * @param array $campaignIDs
 * @return int
 * @deprecated since 5.2.0
 */
function loescheGewaehlteKampagnen(array $campaignIDs): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @param DateTimeImmutable $date
 * @deprecated since 5.2.0
 */
function setzeDetailZeitraum(DateTimeImmutable $date): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}

/**
 * @return false|string
 * @deprecated since 5.2.0
 */
function checkGesamtStatZeitParam()
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param string $month
 * @return string
 * @deprecated since 5.2.0
 */
function mappeENGMonat($month): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return '';
}

/**
 * @return array
 * @deprecated since 5.2.0
 */
function GetTypes(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int $type
 * @return string
 * @deprecated since 5.2.0
 */
function GetKampTypeName(int $type): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param array $stats
 * @param int   $type
 * @return Linechart
 * @deprecated since 5.2.0
 */
function PrepareLineChartKamp(array $stats, int $type): Linechart
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return new Linechart(['active' => false]);
}
