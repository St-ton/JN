<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param \Survey\SurveyQuestion[] $oUmfrageFrage_arr
 * @return int
 * @deprecated since 5.0.0
 */
function bestimmeAnzahlSeiten($oUmfrageFrage_arr)
{
    return 1;
}

/**
 * @param \Survey\SurveyQuestion[] $oUmfrageFrage_arr
 * @return array
 * @deprecated since 5.0.0
 */
function baueSeitenAnfaenge($oUmfrageFrage_arr)
{
    return [];
}

/**
 * @param array $oUmfrageFrage_arr
 * @param int   $nAnzahlFragen
 * @return array
 * @deprecated since 5.0.0
 */
function baueSeitenNavi($oUmfrageFrage_arr, $nAnzahlFragen)
{
    return [];
}

/**
 * @param array $cPost_arr
 * @deprecated since 5.0.0
 */
function speicherFragenInSession($cPost_arr)
{
}

/**
 * @param array $cPost_arr
 * @deprecated since 5.0.0
 */
function findeFragenUndUpdateSession($cPost_arr)
{
}

/**
 * @param array $questions
 * @return array
 * @deprecated since 5.0.0
 */
function findeFragenInSession($questions)
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
 * @param array $cPost_arr
 * @return int
 * @deprecated since 5.0.0
 */
function pruefeEingabe($cPost_arr)
{
    return 0;
}

/**
 * @param int    $kUmfrage
 * @param int    $kKunde
 * @param string $cIP
 * @return bool
 * @deprecated since 5.0.0
 */
function pruefeUserUmfrage($kUmfrage, $kKunde, $cIP = '')
{
    return false;
}

/**
 * @param float $fGuthaben
 * @param int   $kKunde
 * @return bool
 * @deprecated since 5.0.0
 */
function gibKundeGuthaben($fGuthaben, $kKunde)
{
    return false;
}

/**
 * @param int $kUmfrage
 * @return mixed
 * @deprecated since 5.0.0
 */
function holeAktuelleUmfrage($kUmfrage)
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
 * @param int            $kUmfrage
 * @param \Survey\Survey $oUmfrage
 * @param array          $oUmfrageFrageTMP_arr
 * @param array          $oNavi_arr
 * @param int            $nAktuelleSeite
 * @deprecated since 5.0.0
 */
function bearbeiteUmfrageDurchfuehrung(int $kUmfrage, $oUmfrage, &$oUmfrageFrageTMP_arr, &$oNavi_arr, &$nAktuelleSeite)
{
}
