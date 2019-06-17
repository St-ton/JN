<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return int
 * @deprecated since 5.0.0
 */
function bestimmeAnzahlSeiten()
{
    return 1;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function baueSeitenAnfaenge()
{
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function baueSeitenNavi()
{
    return [];
}

/**
 * @deprecated since 5.0.0
 */
function speicherFragenInSession()
{
}

/**
 * @deprecated since 5.0.0
 */
function findeFragenUndUpdateSession()
{
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function findeFragenInSession()
{
    return [];
}

/**
 * @deprecated since 5.0.0
 */
function setzeUmfrageErgebnisse()
{
}

/**
 * Return 0 falls alles in Ordnung
 * Return $kUmfrageFrage falls inkorrekte oder leere Antwort
 *
 * @return int
 * @deprecated since 5.0.0
 */
function pruefeEingabe()
{
    return 0;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeUserUmfrage()
{
    return false;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function gibKundeGuthaben()
{
    return false;
}

/**
 * @return mixed
 * @deprecated since 5.0.0
 */
function holeAktuelleUmfrage()
{
    return false;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeUmfrageUebersicht()
{
    return [];
}

/**
 * @param object $oUmfrage
 * @deprecated since 5.0.0
 */
function bearbeiteUmfrageAuswertung($oUmfrage)
{
}

/**
 * @param int                $kUmfrage
 * @param \JTL\Survey\Survey $oUmfrage
 * @param array              $oUmfrageFrageTMP_arr
 * @param array              $oNavi_arr
 * @param int                $nAktuelleSeite
 * @deprecated since 5.0.0
 */
function bearbeiteUmfrageDurchfuehrung(int $kUmfrage, $oUmfrage, &$oUmfrageFrageTMP_arr, &$oNavi_arr, &$nAktuelleSeite)
{
}
