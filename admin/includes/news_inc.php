<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

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
    $cPlausiValue_arr = [];
    // Betreff prüfen
    if (strlen($cBetreff) === 0) {
        $cPlausiValue_arr['cBetreff'] = 1;
    }
    // Text prüfen
    if (strlen($cText) === 0) {
        $cPlausiValue_arr['cText'] = 1;
    }
    // Kundengruppe prüfen
    if (!is_array($kKundengruppe_arr) || count($kKundengruppe_arr) === 0) {
        $cPlausiValue_arr['kKundengruppe_arr'] = 1;
    }
    // Newskategorie prüfen
    if (!is_array($kNewsKategorie_arr) || count($kNewsKategorie_arr) === 0) {
        $cPlausiValue_arr['kNewsKategorie_arr'] = 1;
    }

    return $cPlausiValue_arr;
}

/**
 * @param string $cName
 * @param int    $nNewskategorieEditSpeichern
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeNewsKategorie($cName, $nNewskategorieEditSpeichern = 0)
{
    return [];
}

/**
 * @deprecated since 4.06
 *
 * @param string $string
 * @return string
 */
function convertDate($string)
{
    list($dDatum, $dZeit) = explode(' ', $string);
    if (substr_count(':', $dZeit) === 2 ) {
        list($nStunde, $nMinute) = explode(':', $dZeit);
    } else {
        list($nStunde, $nMinute, $nSekunde) = explode(':', $dZeit);
    }
    list($nTag, $nMonat, $nJahr) = explode('.', $dDatum);

    return $nJahr . '-' . $nMonat . '-' . $nTag . ' ' . $nStunde . ':' . $nMinute . ':00';
}

/**
 * @param int $kNews
 * @return int|string
 */
function gibLetzteBildNummer($kNews)
{
    $cUploadVerzeichnis = PFAD_ROOT . PFAD_NEWSBILDER;

    $cBild_arr = [];
    if (is_dir($cUploadVerzeichnis . $kNews)) {
        $DirHandle = opendir($cUploadVerzeichnis . $kNews);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $cBild_arr[] = $Datei;
            }
        }
    }
    $nMax       = 0;
    $imageCount = count($cBild_arr);
    if ($imageCount > 0) {
        for ($i = 0; $i < $imageCount; $i++) {
            $cNummer = substr($cBild_arr[$i], 4, (strlen($cBild_arr[$i]) - strpos($cBild_arr[$i], '.')) - 3);

            if ($cNummer > $nMax) {
                $nMax = $cNummer;
            }
        }
    }

    return $nMax;
}

/**
 * @param string $a
 * @param string $b
 * @return int
 */
function cmp($a, $b)
{
    return strcmp($a, $b);
}

/**
 * @param object $a
 * @param object $b
 * @return int
 */
function cmp_obj($a, $b)
{
    return strcmp($a->cName, $b->cName);
}

/**
 * @param string $cMonat
 * @param int    $nJahr
 * @param string $cISOSprache
 * @return string
 */
function mappeDatumName($cMonat, $nJahr, $cISOSprache)
{
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
    list($dDatum, $dUhrzeit)     = explode(' ', $cDateTimeStr);
    list($dJahr, $dMonat, $dTag) = explode('-', $dDatum);
    $oDatum                      = new stdClass();
    $oDatum->Jahr                = (int)$dJahr;
    $oDatum->Monat               = (int)$dMonat;
    $oDatum->Tag                 = (int)$dTag;

    return $oDatum;
}

/**
 * @param int   $kNewsKommentar
 * @param array $cPost_arr
 * @return bool
 * @deprecated since 5.0.0
 */
function speicherNewsKommentar(int $kNewsKommentar, array $cPost_arr)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param  int    $kSprache
 * @param  string $cLimitSQL
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewskategorie($kSprache = null, $cLimitSQL = '')
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsBilder($kNews, $cUploadVerzeichnis)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int    $kNewsKategorie
 * @param string $cUploadVerzeichnis
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsKategorieBilder($kNewsKategorie, $cUploadVerzeichnis)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return [];
}

/**
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return bool
 */
function loescheNewsBilderDir($kNews, $cUploadVerzeichnis)
{
    if (is_dir($cUploadVerzeichnis . $kNews)) {
        $DirHandle = opendir($cUploadVerzeichnis . $kNews);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                unlink($cUploadVerzeichnis . $kNews . '/' . $Datei);
            }
        }
        rmdir($cUploadVerzeichnis . $kNews);

        return true;
    }

    return false;
}

/**
 * @param array $newsCats
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheNewsKategorie(array $newsCats): bool
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param int $kNewsKategorie
 * @param int $kSprache
 * @return stdClass
 * @deprecated since 5.0.0
 */
function editiereNewskategorie(int $kNewsKategorie, int $kSprache)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return new stdClass();
}

/**
 * @param string $cText
 * @param int    $kNews
 * @return string
 * @deprecated since 5.0.0
 */
function parseText($cText, $kNews)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return '';
}

/**
 * @param string $cBildname
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return bool
 * @deprecated since 5.0.0
 */
function loescheNewsBild($cBildname, $kNews, $cUploadVerzeichnis)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return false;
}

/**
 * @param string $cTab
 * @param string $cHinweis
 * @param array  $urlParams
 * @deprecated since 5.0.0
 */
function newsRedirect($cTab = '', $cHinweis = '', $urlParams = null)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
}
