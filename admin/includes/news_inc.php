<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Shop;

/**
 * @param string $cBetreff
 * @param string $cText
 * @param array  $kKundengruppe_arr
 * @param array  $kNewsKategorie_arr
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeNewsPost($cBetreff, $cText, $kKundengruppe_arr, $kNewsKategorie_arr)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    $checks = [];
    // Betreff prüfen
    if (mb_strlen($cBetreff) === 0) {
        $checks['cBetreff'] = 1;
    }
    // Text prüfen
    if (mb_strlen($cText) === 0) {
        $checks['cText'] = 1;
    }
    // Kundengruppe prüfen
    if (!is_array($kKundengruppe_arr) || count($kKundengruppe_arr) === 0) {
        $checks['kKundengruppe_arr'] = 1;
    }
    // Newskategorie prüfen
    if (!is_array($kNewsKategorie_arr) || count($kNewsKategorie_arr) === 0) {
        $checks['kNewsKategorie_arr'] = 1;
    }

    return $checks;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeNewsKategorie()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param string $string
 * @return string
 * @deprecated since 4.06
 */
function convertDate($string)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    [$dDatum, $dZeit]        = explode(' ', $string);
    [$nStunde, $nMinute]     = explode(':', $dZeit);
    [$nTag, $nMonat, $nJahr] = explode('.', $dDatum);

    return $nJahr . '-' . $nMonat . '-' . $nTag . ' ' . $nStunde . ':' . $nMinute . ':00';
}

/**
 * @param string $a
 * @param string $b
 * @return int
 * @deprecated since 5.0.0
 */
function cmp($a, $b)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return strcmp($a, $b);
}

/**
 * @param object $a
 * @param object $b
 * @return int
 */
function cmp_obj($a, $b)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return strcmp($a->cName, $b->cName);
}

/**
 * @param string $cMonat
 * @param int    $nJahr
 * @param string $cISOSprache
 * @return string
 * @deprecated since 5.0.0
 */
function mappeDatumName($cMonat, $nJahr, $cISOSprache)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    $cName = '';

    if ($cISOSprache === 'ger') {
        switch ($cMonat) {
            case '01':
                $cName .= Shop::Lang()->get('january', 'news') . ', ' . $nJahr;
                break;
            case '02':
                $cName .= Shop::Lang()->get('february', 'news') . ', ' . $nJahr;
                break;
            case '03':
                $cName .= Shop::Lang()->get('march', 'news') . ', ' . $nJahr;
                break;
            case '04':
                $cName .= Shop::Lang()->get('april', 'news') . ', ' . $nJahr;
                break;
            case '05':
                $cName .= Shop::Lang()->get('may', 'news') . ', ' . $nJahr;
                break;
            case '06':
                $cName .= Shop::Lang()->get('june', 'news') . ', ' . $nJahr;
                break;
            case '07':
                $cName .= Shop::Lang()->get('july', 'news') . ', ' . $nJahr;
                break;
            case '08':
                $cName .= Shop::Lang()->get('august', 'news') . ', ' . $nJahr;
                break;
            case '09':
                $cName .= Shop::Lang()->get('september', 'news') . ', ' . $nJahr;
                break;
            case '10':
                $cName .= Shop::Lang()->get('october', 'news') . ', ' . $nJahr;
                break;
            case '11':
                $cName .= Shop::Lang()->get('november', 'news') . ', ' . $nJahr;
                break;
            case '12':
                $cName .= Shop::Lang()->get('december', 'news') . ', ' . $nJahr;
                break;
        }
    } else {
        $cName .= date('F', mktime(0, 0, 0, (int)$cMonat, 1, $nJahr)) . ', ' . $nJahr;
    }

    return $cName;
}

/**
 * @param string $cDateTimeStr
 * @return stdClass
 * @deprecated since 4.06
 */
function gibJahrMonatVonDateTime($cDateTimeStr)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    [$dDatum, $dUhrzeit]     = explode(' ', $cDateTimeStr);
    [$dJahr, $dMonat, $dTag] = explode('-', $dDatum);
    $oDatum                  = new stdClass();
    $oDatum->Jahr            = (int)$dJahr;
    $oDatum->Monat           = (int)$dMonat;
    $oDatum->Tag             = (int)$dTag;

    return $oDatum;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function speicherNewsKommentar()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewskategorie()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsBilder()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsKategorieBilder()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int    $kNews
 * @param string $uploadDir
 * @return bool
 */
function loescheNewsBilderDir($kNews, $uploadDir)
{
    if (!is_dir($uploadDir . $kNews)) {
        return false;
    }
    $handle = opendir($uploadDir . $kNews);
    while (($file = readdir($handle)) !== false) {
        if ($file !== '.' && $file !== '..') {
            unlink($uploadDir . $kNews . '/' . $file);
        }
    }
    rmdir($uploadDir . $kNews);

    return true;
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheNewsKategorie(): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @return stdClass
 * @deprecated since 5.0.0
 */
function editiereNewskategorie()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return new stdClass();
}

/**
 * @return string
 * @deprecated since 5.0.0
 */
function parseText()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheNewsBild()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @deprecated since 5.0.0
 */
function newsRedirect()
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
}
