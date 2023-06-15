<?php declare(strict_types=1);

use JTL\Smarty\JTLSmarty;

/**
 * @param int $cronID
 * @return int
 * @deprecated since 5.2.0
 */
function holeCron(int $cronID): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function holeAlleExportformate(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int    $exportID
 * @param string $start
 * @param int    $freq
 * @param int    $cronID
 * @return int
 * @deprecated since 5.2.0
 */
function erstelleExportformatCron(int $exportID, string $start, int $freq, int $cronID = 0): int
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 0;
}

/**
 * @param string $start
 * @return bool
 * @deprecated since 5.2.0
 */
function dStartPruefen($start): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param int[] $cronIDs
 * @return bool
 * @deprecated since 5.2.0
 */
function loescheExportformatCron(array $cronIDs): bool
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return true;
}

/**
 * @param JTLSmarty $smarty
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionErstellen(JTLSmarty $smarty): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 'erstellen';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionEditieren(JTLSmarty $smarty, array &$messages): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $messages['error'] .= __('errorWrongQueue');

    return 'uebersicht';
}

/**
 * @param array $messages
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionLoeschen(array &$messages): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $messages['error'] .= __('errorWrongQueue');

    return 'loeschen_result';
}

/**
 * @param array $messages
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionTriggern(array &$messages): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    $messages['error'] .= __('errorCronStart') . '<br />';

    return 'triggern';
}

/**
 * @param JTLSmarty $smarty
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionFertiggestellt(JTLSmarty $smarty): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 'fertiggestellt';
}

/**
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @return string
 * @deprecated since 5.2.0
 */
function exportformatQueueActionErstellenEintragen(JTLSmarty $smarty, array &$messages): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return 'erstellen';
}

/**
 * @param string     $tab
 * @param array|null $messages
 * @deprecated since 5.2.0
 */
function exportformatQueueRedirect(string $tab = '', array $messages = null): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    exit;
}

/**
 * @param string    $step
 * @param JTLSmarty $smarty
 * @param array     $messages
 * @deprecated since 5.2.0
 */
function exportformatQueueFinalize(string $step, JTLSmarty $smarty, array &$messages): void
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
}

/**
 * @param string $dateStart
 * @param bool   $asTime
 * @return string
 * @deprecated since 5.2.0
 */
function baueENGDate($dateStart, $asTime = false): string
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    [$date, $time]        = explode(' ', $dateStart);
    [$day, $month, $year] = explode('.', $date);

    return $asTime ? $time : $year . '-' . $month . '-' . $day . ' ' . $time;
}

/**
 * @param int $hours
 * @return stdClass[]|bool
 * @deprecated since 5.2.0
 */
function holeExportformatQueueBearbeitet(int $hours = 24)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return stdClass[]
 * @deprecated since 5.2.0
 */
function holeExportformatCron(): array
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int $hours
 * @return bool|string
 * @deprecated since 5.2.0
 */
function rechneUmAlleXStunden(int $hours)
{
    trigger_error(__FUNCTION__ . ' is deprecated and should not be used anymore.', E_USER_DEPRECATED);
    if ($hours <= 0) {
        return false;
    }
    if ($hours > 24) {
        $hours = round($hours / 24);
        if ($hours >= 365) {
            $hours /= 365;
            if ($hours == 1) {
                $hours .= __('year');
            } else {
                $hours .= __('years');
            }
        } elseif ($hours == 1) {
            $hours .= __('day');
        } else {
            $hours .= __('days');
        }
    } elseif ($hours > 1) {
        $hours .= __('hours');
    } else {
        $hours .= __('hour');
    }

    return $hours;
}
