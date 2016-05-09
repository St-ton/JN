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
 */
function pruefeNewsPost($cBetreff, $cText, $kKundengruppe_arr, $kNewsKategorie_arr)
{
    $cPlausiValue_arr = array();
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
 */
function pruefeNewsKategorie($cName, $nNewskategorieEditSpeichern = 0)
{
    $cPlausiValue_arr = array();
    // Name prüfen
    if (strlen($cName) === 0) {
        $cPlausiValue_arr['cName'] = 1;
    }
    // Prüfen ob Name schon vergeben
    if ($nNewskategorieEditSpeichern == 0) {
        $oNewsKategorieTMP = Shop::DB()->query(
            "SELECT kNewsKategorie
                FROM tnewskategorie
                WHERE cName = '" . Shop::DB()->realEscape($cName) . "'", 1
        );

        if (isset($oNewsKategorieTMP->kNewsKategorie) && $oNewsKategorieTMP->kNewsKategorie > 0) {
            $cPlausiValue_arr['cName'] = 2;
        }
    }

    return $cPlausiValue_arr;
}

/**
 * @param string $string
 * @return string
 */
function convertDate($string)
{
    list($dDatum, $dZeit) = explode(' ', $string);
    if (count(explode(':', $dZeit)) === 2) {
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

    $cBild_arr = array();
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
        $cName .= date('F', mktime(0, 0, 0, intval($cMonat), 1, $nJahr)) . ', ' . $nJahr;
    }

    return $cName;
}

/**
 * @param string $cDateTimeStr
 * @return stdClass
 */
function gibJahrMonatVonDateTime($cDateTimeStr)
{
    list($dDatum, $dUhrzeit)     = explode(' ', $cDateTimeStr);
    list($dJahr, $dMonat, $dTag) = explode('-', $dDatum);
    $oDatum                      = new stdClass();
    $oDatum->Jahr                = intval($dJahr);
    $oDatum->Monat               = intval($dMonat);
    $oDatum->Tag                 = intval($dTag);

    return $oDatum;
}

/**
 * @param int   $kNewsKommentar
 * @param array $cPost_arr
 * @return bool
 */
function speicherNewsKommentar($kNewsKommentar, $cPost_arr)
{
    if ($kNewsKommentar > 0) {
        return Shop::DB()->query(
            "UPDATE tnewskommentar
                SET cName = '" . StringHandler::filterXSS($cPost_arr['cName']) . "',
                    cKommentar = '" . StringHandler::filterXSS($cPost_arr['cKommentar']) . "'
                WHERE kNewsKommentar = " . intval($kNewsKommentar), 3
        ) >= 0;
    }

    return false;
}

/**
 * Gibt eine neue Breite und Höhe als Array zurück
 *
 * @param string $cDatei
 * @param int    $nMaxBreite
 * @param int    $nMaxHoehe
 * @return array
 */
function calcRatio($cDatei, $nMaxBreite, $nMaxHoehe)
{
    $path = str_replace(Shop::getURL(), PFAD_ROOT, $cDatei);
    if (file_exists($path)) {
        $cDatei = $path;
    }
    list($ImageBreite, $ImageHoehe) = getimagesize($cDatei);
    if ($ImageBreite === null || $ImageBreite === 0) {
        $ImageBreite = 1;
    }
    if ($ImageHoehe === null || $ImageHoehe === 0) {
        $ImageHoehe = 1;
    }
    $f = min($nMaxBreite / $ImageBreite, $nMaxHoehe / $ImageHoehe, 1);

    return array(round($f * $nMaxBreite), round($f * $nMaxHoehe));
}

/**
 * @return mixed
 */
function holeNewskategorie()
{
    return Shop::DB()->query(
        "SELECT *, DATE_FORMAT(dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            WHERE kSprache = " . (int)$_SESSION['kSprache'], 2
    );
}

/**
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return array
 */
function holeNewsBilder($kNews, $cUploadVerzeichnis)
{
    $oDatei_arr = array();
    $kNews      = (int)$kNews;
    if ($kNews > 0) {
        if (is_dir($cUploadVerzeichnis . $kNews)) {
            $DirHandle = opendir($cUploadVerzeichnis . $kNews);
            while (false !== ($Datei = readdir($DirHandle))) {
                if ($Datei !== '.' && $Datei !== '..') {
                    $oDatei         = new stdClass();
                    $oDatei->cName  = substr($Datei, 0, strpos($Datei, '.'));
                    $oDatei->cURL   = '<img src="' . Shop::getURL() . '/' . PFAD_NEWSBILDER . $kNews . '/' . $Datei . '" />';
                    $oDatei->cDatei = $Datei;

                    $oDatei_arr[] = $oDatei;
                }
            }

            usort($oDatei_arr, 'cmp_obj');
        }
    }

    return $oDatei_arr;
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
 * @param array $kNewsKategorie_arr
 * @return bool
 */
function loescheNewsKategorie($kNewsKategorie_arr)
{
    if (is_array($kNewsKategorie_arr) && count($kNewsKategorie_arr) > 0) {
        foreach ($kNewsKategorie_arr as $kNewsKategorie) {
            $kNewsKategorie = (int)$kNewsKategorie;
            Shop::DB()->delete('tnewskategorie', 'kNewsKategorie', $kNewsKategorie);
            // tseo löschen
            Shop::DB()->delete('tseo', array('cKey', 'kKey'), array('kNewsKategorie', $kNewsKategorie));
            // tnewskategorienews löschen
            Shop::DB()->delete('tnewskategorienews', 'kNewsKategorie', $kNewsKategorie);
        }

        return true;
    }

    return false;
}

/**
 * @param int $kNewsKategorie
 * @param int $kSprache
 * @return stdClass
 */
function editiereNewskategorie($kNewsKategorie, $kSprache)
{
    $oNewsKategorie = new stdClass();
    $kNewsKategorie = (int)$kNewsKategorie;
    $kSprache       = (int)$kSprache;
    if ($kNewsKategorie > 0 && $kSprache > 0) {
        $oNewsKategorie = Shop::DB()->query(
            "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
                tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
                tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, tseo.cSeo,
                DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
                FROM tnewskategorie
                LEFT JOIN tseo ON tseo.cKey = 'kNewsKategorie'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                    AND tseo.kSprache = " . $kSprache . "
                WHERE kNewsKategorie = " . $kNewsKategorie, 1
        );
    }

    return $oNewsKategorie;
}

/**
 * @param string $cText
 * @param int    $kNews
 * @return mixed
 */
function parseText($cText, $kNews)
{
    $cUploadVerzeichnis = PFAD_ROOT . PFAD_NEWSBILDER;
    $cBild_arr          = array();
    if (is_dir($cUploadVerzeichnis . $kNews)) {
        $DirHandle = opendir($cUploadVerzeichnis . $kNews);
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $cBild_arr[] = $Datei;
            }
        }

        closedir($DirHandle);
    }
    usort($cBild_arr, 'cmp');

    for ($i = 1; $i <= count($cBild_arr); $i++) {
        $cText = str_replace("$#Bild" . $i . "#$", '<img alt="" src="' . Shop::getURL() . '/' . PFAD_NEWSBILDER . $kNews . '/' . $cBild_arr[$i - 1] . '" />', $cText);
    }
    if (strpos(end($cBild_arr), 'preview') !== false) {
        $cText = str_replace("$#preview#$", '<img alt="" src="' . Shop::getURL() . '/' . PFAD_NEWSBILDER . $kNews . '/' . $cBild_arr[count($cBild_arr) - 1] . '" />', $cText);
    }

    return str_replace("'", "\'", $cText);
}

/**
 * @param string $cBildname
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return bool
 */
function loescheNewsBild($cBildname, $kNews, $cUploadVerzeichnis)
{
    if (intval($kNews) > 0 && strlen($cBildname) > 0 && is_dir($cUploadVerzeichnis)) {
        if (is_dir($cUploadVerzeichnis . $kNews)) {
            $DirHandle = opendir($cUploadVerzeichnis . $kNews);
            while (false !== ($Datei = readdir($DirHandle))) {
                if ($Datei !== '.' && $Datei !== '..' && substr($Datei, 0, strpos($Datei, '.')) == $cBildname) {
                    unlink($cUploadVerzeichnis . $kNews . '/' . $Datei);
                    closedir($DirHandle);

                    return true;
                }
            }
        }
    }

    return false;
}
