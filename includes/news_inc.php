<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param bool $bActiveOnly
 * @return stdClass
 */
function baueFilterSQL($bActiveOnly = false)
{
    $oSQL              = new stdClass();
    $oSQL->cSortSQL    = '';
    $oSQL->cDatumSQL   = '';
    $oSQL->cNewsKatSQL = '';
    // Sortierung Filter
    if ($_SESSION['NewsNaviFilter']->nSort > 0) {
        switch ($_SESSION['NewsNaviFilter']->nSort) {
            case 1: // Datum absteigend
                $oSQL->cSortSQL = ' ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC';
                break;
            case 2: // Datum aufsteigend
                $oSQL->cSortSQL = ' ORDER BY tnews.dGueltigVon';
                break;
            case 3: // Name a ... z
                $oSQL->cSortSQL = ' ORDER BY tnews.cBetreff';
                break;
            case 4: // Name z ... a
                $oSQL->cSortSQL = ' ORDER BY tnews.cBetreff DESC';
                break;
            case 5: // Anzahl Kommentare absteigend
                $oSQL->cSortSQL = ' ORDER BY nNewsKommentarAnzahl DESC';
                break;
            case 6: // Anzahl Kommentare aufsteigend
                $oSQL->cSortSQL = ' ORDER BY nNewsKommentarAnzahl';
                break;
        }
    } elseif ($_SESSION['NewsNaviFilter']->nSort == -1) {
        // Standard
        $oSQL->cSortSQL = ' ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC';
    }
    // Datum Filter
    $oSQL->cDatumSQL = '';
    if ($_SESSION['NewsNaviFilter']->cDatum != -1 && strlen($_SESSION['NewsNaviFilter']->cDatum) > 0) {
        $_date = explode('-', $_SESSION['NewsNaviFilter']->cDatum);
        if (count($_date) > 1) {
            list($nMonat, $nJahr) = $_date;
            $oSQL->cDatumSQL      = " AND MONTH(tnews.dGueltigVon) = '" . (int)$nMonat . "' 
                                      AND YEAR(tnews.dGueltigVon) = '" . (int)$nJahr . "'";
        } else { //invalid date given/xss -> reset to -1
            $_SESSION['NewsNaviFilter']->cDatum = -1;
        }
    }
    // NewsKat Filter
    $oSQL->cNewsKatSQL = ' JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews';
    $cNewsCats         = implode(',', News::getNewsCatAndSubCats($_SESSION['NewsNaviFilter']->nNewsKat, Shop::getLanguageID(), false, true));

    if ($_SESSION['NewsNaviFilter']->nNewsKat > 0) {
        $oSQL->cNewsKatSQL = " JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews
                               AND tnewskategorienews.kNewsKategorie IN (" . $cNewsCats . ")";
    }

    if ($bActiveOnly) {
        $oSQL->cNewsKatSQL .= ' JOIN tnewskategorie 
                                    ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
                                    AND tnewskategorie.nAktiv = 1';
    }

    return $oSQL;
}

/**
 * Prüft ob eine Kunde bereits einen Kommentar zu einer News geschrieben hat.
 * Falls Ja => return false
 * Falls Nein => return true
 *
 * @param string $cKommentar
 * @param string $cName
 * @param string $cEmail
 * @param int    $kNews
 * @param array  $Einstellungen
 * @return array
 * @deprecated since 5.0.0
 */
function pruefeKundenKommentar($cKommentar, $cName = '', $cEmail = '', $kNews, $Einstellungen)
{
    trigger_error(__METHOD__ . ' is deprecated. Use \News\Controller::checkComment() instead.', E_USER_DEPRECATED);
    if (!isset($_POST['cEmail'])) {
        $_POST['cEmail'] = $cEmail;
    }
    if (!isset($_POST['cName'])) {
        $_POST['cName'] = $cEmail;
    }
    $_POST['cKommentar'] = $cKommentar;

    return \News\Controller::checkComment($_POST, (int)$kNews, $Einstellungen);
}

/**
 * @param array $nPlausiValue_arr
 * @return string
 * @deprecated since 5.0.0
 */
function gibNewskommentarFehler($nPlausiValue_arr)
{
    trigger_error(__METHOD__ . ' is deprecated. Use \News\Controller::getCommentErrors() instead.', E_USER_DEPRECATED);
    return \News\Controller::getCommentErrors($nPlausiValue_arr);
}

/**
 * @param string $cDatumSQL
 * @param bool   $bActiveOnly
 * @return mixed
 */
function holeNewsKategorien($cDatumSQL, $bActiveOnly = false)
{
    $kSprache     = Shop::getLanguageID();
    $cSQL         = '';
    $activeFilter = $bActiveOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';
    if (strlen($cDatumSQL) > 0) {
        $cSQL = '   JOIN tnewskategorienews 
                        ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    JOIN tnews 
                        ON tnews.kNews = tnewskategorienews.kNews
                    ' . $cDatumSQL;
    }

    return Shop::Container()->getDB()->query(
        "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
            tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, 
            tnewskategorie.cPreviewImage, tseo.cSeo,
            DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y  %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            " . $cSQL . "
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = tnewskategorie.kNewsKategorie
                AND tseo.kSprache = " . $kSprache . "
                AND tnewskategorie.kSprache = " . $kSprache . "
            WHERE tnewskategorie.kSprache = " . $kSprache
            . $activeFilter . "
            GROUP BY tnewskategorie.kNewsKategorie
            ORDER BY tnewskategorie.nSort",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param array $oDatum_arr
 * @return array
 */
function baueDatum($oDatum_arr)
{
    $oDatumTMP_arr = [];
    if (is_array($oDatum_arr) && count($oDatum_arr) > 0) {
        foreach ($oDatum_arr as $oDatum) {
            $oTMP            = new stdClass();
            $oTMP->cWert     = $oDatum->nMonat . '-' . $oDatum->nJahr;
            $oTMP->cName     = mappeDatumName((string)$oDatum->nMonat, (int)$oDatum->nJahr, Shop::getLanguageCode());
            $oDatumTMP_arr[] = $oTMP;
        }
    }

    return $oDatumTMP_arr;
}

/**
 * @param string $cMonat
 * @param string $nJahr
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
 * @param object $oNewsNaviFilter
 * @param array  $oNewsUebersicht_arr
 * @return string
 * @deprecated since 4.04
 */
function baueNewsMetaTitle($oNewsNaviFilter, $oNewsUebersicht_arr)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cMetaTitle = baueNewsMetaStart($oNewsNaviFilter);
    if (is_array($oNewsUebersicht_arr) && count($oNewsUebersicht_arr) > 0) {
        $nCount = 3;
        if (count($oNewsUebersicht_arr) < $nCount) {
            $nCount = count($oNewsUebersicht_arr);
        }
        for ($i = 0; $i < $nCount; $i++) {
            if ($i > 0) {
                $cMetaTitle .= ' - ' . $oNewsUebersicht_arr[$i]->cBetreff;
            } else {
                $cMetaTitle .= $oNewsUebersicht_arr[$i]->cBetreff;
            }
        }
    }

    return $cMetaTitle;
}

/**
 * @param object $oNewsNaviFilter
 * @param array  $oNewsUebersicht_arr
 * @return string
 * @deprecated since 4.04
 */
function baueNewsMetaDescription($oNewsNaviFilter, $oNewsUebersicht_arr)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cMetaDescription = baueNewsMetaStart($oNewsNaviFilter);
    if (is_array($oNewsUebersicht_arr) && count($oNewsUebersicht_arr) > 0) {
        shuffle($oNewsUebersicht_arr);
        $nCount = 12;
        if (count($oNewsUebersicht_arr) < $nCount) {
            $nCount = count($oNewsUebersicht_arr);
        }
        for ($i = 0; $i < $nCount; $i++) {
            if ($i > 0) {
                $cMetaDescription .= ' - ' . $oNewsUebersicht_arr[$i]->cBetreff;
            } else {
                $cMetaDescription .= $oNewsUebersicht_arr[$i]->cBetreff;
            }
        }
    }

    return $cMetaDescription;
}

/**
 * @param object $oNewsNaviFilter
 * @param array  $oNewsUebersicht_arr
 * @return string
 * @deprecated since 5.0.0
 */
function baueNewsMetaKeywords($oNewsNaviFilter, $oNewsUebersicht_arr)
{
    $cMetaKeywords = '';
    if (is_array($oNewsUebersicht_arr) && count($oNewsUebersicht_arr) > 0) {
        $nCount = 6;
        if (count($oNewsUebersicht_arr) < $nCount) {
            $nCount = count($oNewsUebersicht_arr);
        }
        for ($i = 0; $i < $nCount; $i++) {
            if ($i > 0) {
                $cMetaKeywords .= ', ' . $oNewsUebersicht_arr[$i]->cMetaKeywords;
            } else {
                $cMetaKeywords .= $oNewsUebersicht_arr[$i]->cMetaKeywords;
            }
        }
    }

    return $cMetaKeywords;
}

/**
 * @param object $oNewsNaviFilter
 * @return string
 * @deprecated since 4.04
 */
function baueNewsMetaStart($oNewsNaviFilter)
{
    trigger_error(__FUNCTION__ . ' is deprecated.', E_USER_DEPRECATED);
    $cMetaStart = Shop::Lang()->get('overview', 'news');
    // Datumfilter gesetzt
    if ($oNewsNaviFilter->cDatum != -1) {
        $cMetaStart .= ' ' . $oNewsNaviFilter->cDatum;
    }
    // Kategoriefilter gesetzt
    if ($oNewsNaviFilter->nNewsKat != -1) {
        $oNewsKat = Shop::Container()->getDB()->select(
            'tnewskategorie',
            'kNewsKategorie',
            (int)$oNewsNaviFilter->nNewsKat,
            'kSprache',
            Shop::getLanguageID()
        );
        if (isset($oNewsKat->kNewsKategorie) && $oNewsKat->kNewsKategorie > 0) {
            $cMetaStart .= ' ' . $oNewsKat->cName;
        }
    }

    return $cMetaStart . ': ';
}

/**
 * @param JTLSmarty   $smarty
 * @param string|null $AktuelleSeite
 * @param string      $cCanonicalURL
 * @deprecated since 5.0.0
 */
function baueNewsKruemel($smarty, $AktuelleSeite, &$cCanonicalURL)
{
}

/**
 * @param int  $kNews
 * @param bool $bActiveOnly
 * @return stdClass|null
 */
function getNewsArchive(int $kNews, bool $bActiveOnly = false)
{
    $activeFilter = $bActiveOnly ? ' AND tnews.nAktiv = 1 ' : '';

    return Shop::Container()->getDB()->query(
        "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, 
            tnews.cVorschauText, tnews.cPreviewImage, tnews.cMetaTitle, tnews.cMetaDescription, 
            tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tnews.dGueltigVon, tseo.cSeo,
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS Datum, 
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de
            FROM tnews
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . Shop::getLanguageID() . "
            WHERE tnews.kNews = " . $kNews . " 
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND tnews.kSprache = " . Shop::getLanguageID()
                . $activeFilter,
        \DB\ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int  $kNewsKategorie
 * @param bool $bActiveOnly
 * @return mixed
 */
function getCurrentNewsCategory(int $kNewsKategorie, bool $bActiveOnly = false)
{
    $activeFilter = $bActiveOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';

    return Shop::Container()->getDB()->queryPrepared(
        "SELECT tnewskategorie.cName, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription, tseo.cSeo
            FROM tnewskategorie
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = :cat
                AND tseo.kSprache = :lid
            WHERE tnewskategorie.kNewsKategorie = :cat" . $activeFilter,
        [
            'cat' => $kNewsKategorie,
            'lid' => Shop::getLanguageID()
        ],
        \DB\ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int $kNews
 * @return mixed
 */
function getNewsCategory(int $kNews)
{
    $newsCategories = \Functional\map(
        \Functional\pluck(Shop::Container()->getDB()->selectAll(
            'tnewskategorienews',
            'kNews',
            $kNews,
            'kNewsKategorie'
        ), 'kNewsKategorie'),
        function ($e) { return (int)$e; }
    );

    return Shop::Container()->getDB()->query(
        "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
            tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung,
            tnewskategorie.cPreviewImage, tseo.cSeo,
            DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            LEFT JOIN tnewskategorienews 
                ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = tnewskategorie.kNewsKategorie
                AND tseo.kSprache = " . Shop::getLanguageID() . "
            WHERE tnewskategorie.kSprache = " . Shop::getLanguageID() . "
                AND tnewskategorienews.kNewsKategorie IN (" . implode(',', $newsCategories) . ")
                AND tnewskategorie.nAktiv = 1
            GROUP BY tnewskategorie.kNewsKategorie
            ORDER BY tnewskategorie.nSort DESC",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param int    $kNews
 * @param string $cLimitSQL
 * @return mixed
 */
function getNewsComments(int $kNews, $cLimitSQL)
{
    return Shop::Container()->getDB()->query(
        "SELECT *, DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de
            FROM tnewskommentar
            WHERE tnewskommentar.kNews = " . $kNews . "
                AND tnewskommentar.nAktiv = 1
            ORDER BY tnewskommentar.dErstellt DESC
            LIMIT " . $cLimitSQL,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param int $kNews
 * @return mixed
 */
function getCommentCount(int $kNews)
{
    return Shop::Container()->getDB()->queryPrepared(
        'SELECT COUNT(*) AS nAnzahl
            FROM tnewskommentar
            WHERE kNews = :nid
            AND nAktiv = 1',
        ['nid' => $kNews],
        \DB\ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param int $kNewsMonatsUebersicht
 * @return mixed
 */
function getMonthOverview(int $kNewsMonatsUebersicht)
{
    return Shop::Container()->getDB()->queryPrepared(
        "SELECT tnewsmonatsuebersicht.*, tseo.cSeo
            FROM tnewsmonatsuebersicht
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNewsMonatsUebersicht'
                AND tseo.kKey = :nmi
                AND tseo.kSprache = :lid
            WHERE tnewsmonatsuebersicht.kNewsMonatsUebersicht = :nmi",
        [
            'nmi' => $kNewsMonatsUebersicht,
            'lid' => Shop::getLanguageID()
        ],
        \DB\ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param object $oSQL
 * @param string $cLimitSQL
 * @return mixed
 */
function getNewsOverview($oSQL, $cLimitSQL)
{
    return Shop::Container()->getDB()->query(
        "SELECT tseo.cSeo, tnews.*, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dErstellt_de, 
            COUNT(*) AS nAnzahl, COUNT(DISTINCT(tnewskommentar.kNewsKommentar)) AS nNewsKommentarAnzahl
            FROM tnews
            LEFT JOIN tseo 
                ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . Shop::getLanguageID() . "
            LEFT JOIN tnewskommentar 
                ON tnewskommentar.kNews = tnews.kNews 
                AND tnewskommentar.nAktiv = 1
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND tnews.kSprache = " . Shop::getLanguageID() . "
                " . $oSQL->cDatumSQL . "
            GROUP BY tnews.kNews
            " . $oSQL->cSortSQL . "
            LIMIT " . $cLimitSQL,
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param object $oSQL
 * @return mixed
 */
function getFullNewsOverview($oSQL)
{
    return Shop::Container()->getDB()->query(
        "SELECT count(DISTINCT(tnews.kNews)) AS nAnzahl
            FROM tnews
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                " . $oSQL->cDatumSQL . "
                AND tnews.kSprache = " . Shop::getLanguageID(),
        \DB\ReturnType::SINGLE_OBJECT
    );
}

/**
 * @param object $oSQL
 * @return mixed
 */
function getNewsDateArray($oSQL)
{
    return Shop::Container()->getDB()->query(
      "SELECT month(tnews.dGueltigVon) AS nMonat, year(tnews.dGueltigVon) AS nJahr
            FROM tnews
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND tnews.kSprache = " . Shop::getLanguageID() . "
            GROUP BY nJahr, nMonat
            ORDER BY dGueltigVon DESC",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
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
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return array
 */
function holeNewsBilder(int $kNews, $cUploadVerzeichnis)
{
    $oDatei_arr = [];
    if ($kNews > 0 && is_dir($cUploadVerzeichnis . $kNews)) {
        $DirHandle    = opendir($cUploadVerzeichnis . $kNews);
        $imageBaseURL = Shop::getURL() . '/';
        while (false !== ($Datei = readdir($DirHandle))) {
            if ($Datei !== '.' && $Datei !== '..') {
                $oDatei           = new stdClass();
                $oDatei->cName    = substr($Datei, 0, strpos($Datei, '.'));
                $oDatei->cURL     = PFAD_NEWSBILDER . $kNews . '/' . $Datei;
                $oDatei->cURLFull = $imageBaseURL . PFAD_NEWSBILDER . $kNews . '/' . $Datei;
                $oDatei->cDatei   = $Datei;

                $oDatei_arr[] = $oDatei;
            }
        }

        usort($oDatei_arr, 'cmp_obj');
    }

    return $oDatei_arr;
}
