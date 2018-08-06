<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param bool $bActiveOnly
 * @return stdClass
 * @deprecated since 5.0.0
 */
function baueFilterSQL($bActiveOnly = false)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return \News\Controller::getFilterSQL($bActiveOnly);
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
function pruefeKundenKommentar($cKommentar, $cName, $cEmail, $kNews, $Einstellungen)
{
    trigger_error(__METHOD__ . ' is deprecated. Use \News\Controller::checkComment() instead.', E_USER_DEPRECATED);
    if (!isset($_POST['cEmail'])) {
        $_POST['cEmail'] = $cEmail;
    }
    if (!isset($_POST['cName'])) {
        $_POST['cName'] = $cName;
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
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsKategorien($cDatumSQL, $bActiveOnly = false)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @param array $dates
 * @return array
 * @deprecated since 5.0.0
 */
function baueDatum($dates)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    $res = [];
    foreach ($dates as $oDatum) {
        $oTMP            = new stdClass();
        $oTMP->cWert     = $oDatum->nMonat . '-' . $oDatum->nJahr;
        $oTMP->cName     = mappeDatumName((string)$oDatum->nMonat, (int)$oDatum->nJahr, Shop::getLanguageCode());
        $res[] = $oTMP;
    }

    return $res;
}

/**
 * @param string $cMonat
 * @param string $nJahr
 * @param string $cISOSprache
 * @return string
 * @deprecated since 5.0.0
 */
function mappeDatumName($cMonat, $nJahr, $cISOSprache)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return \News\Controller::mapDateName($cMonat, $nJahr, $cISOSprache);
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
    return '';
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
    return '';
}

/**
 * @param object $oNewsNaviFilter
 * @param array  $oNewsUebersicht_arr
 * @return string
 * @deprecated since 5.0.0
 */
function baueNewsMetaKeywords($oNewsNaviFilter, $oNewsUebersicht_arr)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
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
    return '';
}

/**
 * @param JTLSmarty   $smarty
 * @param string|null $AktuelleSeite
 * @param string      $cCanonicalURL
 * @deprecated since 5.0.0
 */
function baueNewsKruemel($smarty, $AktuelleSeite, &$cCanonicalURL)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
}

/**
 * @param int  $kNews
 * @param bool $bActiveOnly
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function getNewsArchive(int $kNews, bool $bActiveOnly = false)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    $activeFilter = $bActiveOnly ? ' AND tnews.nAktiv = 1 ' : '';

    return Shop::Container()->getDB()->query(
        "SELECT tnews.kNews, t.languageID AS kSprache, tnews.cKundengruppe, t.title AS cBetreff, t.content AS cText, 
            t.preview AS cVorschauText, tnews.cPreviewImage, t.metaTitle AS cMetaTitle, t.metaDescription AS cMetaDescription, 
            t.metaKeywords AS cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tnews.dGueltigVon, tseo.cSeo,
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS Datum, 
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de
            FROM tnews
            JOIN tnewssprache t 
                ON tnews.kNews = t.kNews
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
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function getCurrentNewsCategory(int $kNewsKategorie, bool $bActiveOnly = false)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @return array
 * @deprecated since 5.0.0
 */
function getNewsCategory(int $kNews)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @return array
 * @deprecated since 5.0.0
 */
function getNewsComments(int $kNews, $cLimitSQL)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @return stdClass
 * @deprecated since 5.0.0
 */
function getCommentCount(int $kNews)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @return stdClass|null
 * @deprecated since 5.0.0
 */
function getMonthOverview(int $kNewsMonatsUebersicht)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @return array
 * @deprecated since 5.0.0
 */
function getNewsOverview($oSQL, $cLimitSQL)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
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
 * @return stdClass
 * @deprecated since 5.0.0
 */
function getFullNewsOverview($oSQL)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::Container()->getDB()->query(
        "SELECT COUNT(DISTINCT(tnews.kNews)) AS nAnzahl
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
 * @return array
 * @deprecated since 5.0.0
 */
function getNewsDateArray($oSQL)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return Shop::Container()->getDB()->query(
        "SELECT MONTH(tnews.dGueltigVon) AS nMonat, YEAR(tnews.dGueltigVon) AS nJahr
            FROM tnews
            JOIN tnewssprache t
                ON tnews.kNews = t.kNews
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv = 1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' 
                    OR FIND_IN_SET('" . Session::CustomerGroup()->getID()
                        . "', REPLACE(tnews.cKundengruppe, ';', ',')) > 0)
                AND t.languageID = " . Shop::getLanguageID() . "
            GROUP BY nJahr, nMonat
            ORDER BY dGueltigVon DESC",
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

/**
 * @param object $a
 * @param object $b
 * @return int
 * @deprecated since 5.0.0
 */
function cmp_obj($a, $b)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
    return strcmp($a->cName, $b->cName);
}

/**
 * @param int    $kNews
 * @param string $cUploadVerzeichnis
 * @return array
 * @deprecated since 5.0.0
 */
function holeNewsBilder(int $kNews, $cUploadVerzeichnis)
{
    trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);
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
