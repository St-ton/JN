<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @param int  $nAktuelleSeite
 * @param bool $bActiveOnly
 * @return stdClass
 */
function baueFilterSQL($nAktuelleSeite = 1, $bActiveOnly = false)
{
    $oSQL              = new stdClass();
    $oSQL->cSortSQL    = '';
    $oSQL->cAnzahlSQL  = '';
    $oSQL->cDatumSQL   = '';
    $oSQL->cNewsKatSQL = '';
    // Anzahl Filter
    if ($_SESSION['NewsNaviFilter']->nAnzahl > 0) {
        $oSQL->cAnzahlSQL = ' LIMIT ' . (($nAktuelleSeite - 1) * $_SESSION['NewsNaviFilter']->nAnzahl) . ', ' . $_SESSION['NewsNaviFilter']->nAnzahl;
    } elseif ($_SESSION['NewsNaviFilter']->nAnzahl == -1) { // Standard
        $oSQL->cAnzahlSQL = '';
    }
    // Sortierung Filter
    if ($_SESSION['NewsNaviFilter']->nSort > 0) {
        switch ($_SESSION['NewsNaviFilter']->nSort) {
            case 1: // Datum absteigend
                $oSQL->cSortSQL = " ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC";
                break;
            case 2: // Datum aufsteigend
                $oSQL->cSortSQL = " ORDER BY tnews.dGueltigVon";
                break;
            case 3: // Name a ... z
                $oSQL->cSortSQL = " ORDER BY tnews.cBetreff";
                break;
            case 4: // Name z ... a
                $oSQL->cSortSQL = " ORDER BY tnews.cBetreff DESC";
                break;
            case 5: // Anzahl Kommentare absteigend
                $oSQL->cSortSQL = " ORDER BY nNewsKommentarAnzahl DESC";
                break;
            case 6: // Anzahl Kommentare aufsteigend
                $oSQL->cSortSQL = " ORDER BY nNewsKommentarAnzahl";
                break;
        }
    } elseif ($_SESSION['NewsNaviFilter']->nSort == -1) {
        // Standard
        $oSQL->cSortSQL = " ORDER BY tnews.dGueltigVon DESC, tnews.dErstellt DESC";
    }
    // Datum Filter
    $oSQL->cDatumSQL = '';
    if (strlen($_SESSION['NewsNaviFilter']->cDatum) > 0 && $_SESSION['NewsNaviFilter']->cDatum != -1) {
        $_date = explode('-', $_SESSION['NewsNaviFilter']->cDatum);
        if (count($_date) > 1) {
            list($nMonat, $nJahr) = $_date;
            $oSQL->cDatumSQL      = " AND MONTH(tnews.dGueltigVon)='" . (int)$nMonat . "' AND YEAR(tnews.dGueltigVon)='" . (int)$nJahr . "'";
        } else { //invalid date given/xss -> reset to -1
            $_SESSION['NewsNaviFilter']->cDatum = -1;
        }
    }
    // NewsKat Filter
    if ($_SESSION['NewsNaviFilter']->nNewsKat > 0) {
        $oSQL->cNewsKatSQL = " JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews
                                    AND tnewskategorienews.kNewsKategorie = " . (int)$_SESSION['NewsNaviFilter']->nNewsKat;
    } else {
        $oSQL->cNewsKatSQL = ' JOIN tnewskategorienews ON tnewskategorienews.kNews = tnews.kNews';
    }

    if ($bActiveOnly) {
        $oSQL->cNewsKatSQL .= ' JOIN tnewskategorie ON tnewskategorie.kNewsKategorie = tnewskategorienews.kNewsKategorie
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
 */
function pruefeKundenKommentar($cKommentar, $cName = '', $cEmail = '', $kNews, $Einstellungen)
{
    $nPlausiValue_arr = array();
    $conf             = Shop::getSettings(array(CONF_NEWS));
    // Kommentar prüfen
    if (strlen($cKommentar) === 0) {
        $nPlausiValue_arr['cKommentar'] = 1;
    }
    if (strlen($cKommentar) > 1000) {
        $nPlausiValue_arr['cKommentar'] = 2;
    }
    if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0 && $kNews > 0) {
        // Kunde ist eingeloggt
        $oNewsKommentar = Shop::DB()->query(
            "SELECT count(*) AS nAnzahl
                FROM tnewskommentar
                WHERE kNews = " . (int)$kNews . "
                    AND kKunde = " . (int)$_SESSION['Kunde']->kKunde, 1
        );

        if ($oNewsKommentar->nAnzahl > (int)$Einstellungen['news']['news_kommentare_anzahlprobesucher'] &&
            (int)$Einstellungen['news']['news_kommentare_anzahlprobesucher'] !== 0) {
            $nPlausiValue_arr['nAnzahl'] = 1;
        }

        $cEmail = $_SESSION['Kunde']->cMail;
    } else {
        // Kunde ist nicht eingeloggt - Name prüfen
        if (strlen($cName) === 0) {
            $nPlausiValue_arr['cName'] = 1;
        }
        // Email prüfen
        if (!valid_email($cEmail)) {
            $nPlausiValue_arr['cEmail'] = 1;
        }
        if (empty($_SESSION['Kunde']->kKunde) && (!isset($_SESSION['bAnti_spam_already_checked']) || $_SESSION['bAnti_spam_already_checked'] !== true) && isset($conf['news']['news_sicherheitscode']) && $conf['news']['news_sicherheitscode'] !== 'N') {
            // reCAPTCHA
            if (isset($_POST['g-recaptcha-response'])) {
                if (!validateReCaptcha($_POST['g-recaptcha-response'])) {
                    $nPlausiValue_arr['captcha'] = 1;
                }
            } else {
                if (empty($_POST['captcha'])) {
                    $nPlausiValue_arr['captcha'] = 1;
                }
                if (!isset($_POST['md5']) || !$_POST['md5'] || ($_POST['md5'] !== md5(PFAD_ROOT . $_POST['captcha']))) {
                    $nPlausiValue_arr['captcha'] = 2;
                }
                if ($conf['news']['news_sicherheitscode'] == 5) {
                    $nPlausiValue_arr['captcha'] = 2;
                    if (validToken()) {
                        unset($nPlausiValue_arr['captcha']);
                    }
                }
            }
        }
    }
    if ((!isset($nPlausiValue_arr['cName']) || !$nPlausiValue_arr['cName']) && pruefeEmailblacklist($cEmail)) {
        $nPlausiValue_arr['cEmail'] = 2;
    }

    return $nPlausiValue_arr;
}

/**
 * @param array $nPlausiValue_arr
 * @return string
 */
function gibNewskommentarFehler($nPlausiValue_arr)
{
    $cFehler = '';
    // Kommentarfeld ist leer
    if (isset($nPlausiValue_arr['cKommentar']) && $nPlausiValue_arr['cKommentar'] == 1) {
        $cFehler .= Shop::Lang()->get('newscommentMissingtext', 'errorMessages') . '<br />';
    } elseif (isset($nPlausiValue_arr['cKommentar']) && $nPlausiValue_arr['cKommentar'] == 2) {
        // Kommentar ist länger als 1000 Zeichen
        $cFehler .= Shop::Lang()->get('newscommentLongtext', 'errorMessages') . '<br />';
    }
    // Kunde hat bereits einen Newskommentar zu der aktuellen News geschrieben
    if (isset($nPlausiValue_arr['nAnzahl']) && $nPlausiValue_arr['nAnzahl'] == 1) {
        $cFehler .= Shop::Lang()->get('newscommentAlreadywritten', 'errorMessages') . '<br />';
    }
    // Kunde ist nicht eingeloggt und das Feld Name oder Email ist leer
    if ((isset($nPlausiValue_arr['cName']) && $nPlausiValue_arr['cName'] == 1) || (isset($nPlausiValue_arr['cEmail']) && $nPlausiValue_arr['cEmail'] == 1)) {
        $cFehler .= Shop::Lang()->get('newscommentMissingnameemail', 'errorMessages') . '<br />';
    }
    // Emailadresse ist auf der Blacklist
    if (isset($nPlausiValue_arr['cEmail']) && $nPlausiValue_arr['cEmail'] == 2) {
        $cFehler .= Shop::Lang()->get('kwkEmailblocked', 'errorMessages') . '<br />';
    }

    return $cFehler;
}

/**
 * @param string $cDatumSQL
 * @param bool   $bActiveOnly
 * @return mixed
 */
function holeNewsKategorien($cDatumSQL, $bActiveOnly = false)
{
    $kSprache     = (int)$_SESSION['kSprache'];
    $cSQL         = '';
    $activeFilter = $bActiveOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';
    if (strlen($cDatumSQL) > 0) {
        $cSQL = "   JOIN tnewskategorienews ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
                    JOIN tnews ON tnews.kNews = tnewskategorienews.kNews
                    " . $cDatumSQL;
    }

    return Shop::DB()->query(
        "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
            tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, tseo.cSeo,
            DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y  %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            " . $cSQL . "
            LEFT JOIN tseo ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = tnewskategorie.kNewsKategorie
                AND tseo.kSprache = " . $kSprache . "
                AND tnewskategorie.kSprache = " . $kSprache . "
            WHERE tnewskategorie.kSprache = " . $kSprache
            . $activeFilter . "
            GROUP BY tnewskategorie.kNewsKategorie
            ORDER BY tnewskategorie.nSort", 2
    );
}

/**
 * @param array $oDatum_arr
 * @return array
 */
function baueDatum($oDatum_arr)
{
    $oDatumTMP_arr = array();
    if (is_array($oDatum_arr) && count($oDatum_arr) > 0) {
        foreach ($oDatum_arr as $oDatum) {
            $oTMP            = new stdClass();
            $oTMP->cWert     = $oDatum->nMonat . '-' . $oDatum->nJahr;
            $oTMP->cName     = mappeDatumName(strval($oDatum->nMonat), (int)$oDatum->nJahr, $_SESSION['cISOSprache']);
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

    if ($cISOSprache == 'ger') {
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
 */
function baueNewsMetaTitle($oNewsNaviFilter, $oNewsUebersicht_arr)
{
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
 */
function baueNewsMetaDescription($oNewsNaviFilter, $oNewsUebersicht_arr)
{
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
 */
function baueNewsMetaKeywords($oNewsNaviFilter, $oNewsUebersicht_arr)
{
    $cMetaKeywords = baueNewsMetaStart($oNewsNaviFilter);
    if (is_array($oNewsUebersicht_arr) && count($oNewsUebersicht_arr) > 0) {
        $nCount = 6;
        if (count($oNewsUebersicht_arr) < $nCount) {
            $nCount = count($oNewsUebersicht_arr);
        }
        for ($i = 0; $i < $nCount; $i++) {
            if ($i > 0) {
                $cMetaKeywords .= ' - ' . $oNewsUebersicht_arr[$i]->cBetreff;
            } else {
                $cMetaKeywords .= $oNewsUebersicht_arr[$i]->cBetreff;
            }
        }
    }

    return $cMetaKeywords;
}

/**
 * @param object $oNewsNaviFilter
 * @return string
 */
function baueNewsMetaStart($oNewsNaviFilter)
{
    $cMetaStart = Shop::Lang()->get('overview', 'news');
    // Datumfilter gesetzt
    if ($oNewsNaviFilter->cDatum != -1) {
        $cMetaStart .= ' ' . $oNewsNaviFilter->cDatum;
    }
    // Kategoriefilter gesetzt
    if ($oNewsNaviFilter->nNewsKat != -1) {
        $oNewsKat = Shop::DB()->query(
            "SELECT cName, kNewsKategorie
                FROM tnewskategorie
                WHERE kNewsKategorie = " . (int)$oNewsNaviFilter->nNewsKat . "
                    AND kSprache = " . (int)$_SESSION['kSprache'], 1
        );

        if (isset($oNewsKat->kNewsKategorie) && $oNewsKat->kNewsKategorie > 0) {
            $cMetaStart .= ' ' . $oNewsKat->cName;
        }
    }

    return $cMetaStart . ': ';
}

/**
 *
 */
function baueNewsKruemel($smarty, $AktuelleSeite , &$cCanonicalURL)
{
    $oLink = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_NEWS, 1);

    if (isset($oLink->kLink) && $oLink->kLink > 0) {
        //hole Link
        $linkHelper    = LinkHelper::getInstance();
        $Link          = $linkHelper->getPageLink($oLink->kLink);
        $Link->Sprache = $linkHelper->getPageLink($oLink->kLink);
        //url
        global $sprachURL, $requestURL;
        $requestURL = baueURL($Link, URLART_SEITE);
        $sprachURL  = baueSprachURLS($Link, URLART_SEITE);
        // Canonical
        if (strpos($requestURL, '.php') === false || !SHOP_SEO) {
            $cCanonicalURL = Shop::getURL() . '/' . $requestURL;
        }
        if (!isset($AktuelleSeite)) {
            $AktuelleSeite = null;
        }
        $smarty->assign('Navigation', createNavigation($AktuelleSeite, 0, 0, $Link->Sprache->cName, $requestURL));
    } else {
        // Canonical
        $cCanonicalURL = Shop::getURL() . '/news.php';
        $smarty->assign('Navigation', createNavigation($AktuelleSeite, 0, 0, Shop::Lang()->get('news', 'breadcrumb'), 'news.php'));
    }
}

/**
 * @param int  $kNews
 * @param bool $bActiveOnly
 * @return mixed
 */
function getNewsArchive($kNews, $bActiveOnly = false)
{
    $activeFilter = $bActiveOnly ? ' AND tnews.nAktiv = 1 ' : '';

    return Shop::DB()->query(
        "SELECT tnews.kNews, tnews.kSprache, tnews.cKundengruppe, tnews.cBetreff, tnews.cText, tnews.cVorschauText, tnews.cPreviewImage, tnews.cMetaTitle,
            tnews.cMetaDescription, tnews.cMetaKeywords, tnews.nAktiv, tnews.dErstellt, tseo.cSeo,
            DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS Datum, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dGueltigVon_de
            FROM tnews
            LEFT JOIN tseo ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            WHERE tnews.kNews = " . (int)$kNews . " AND (tnews.cKundengruppe LIKE '%;-1;%' OR tnews.cKundengruppe LIKE '%;" . (int)$_SESSION['Kundengruppe']->kKundengruppe . ";%')
                AND tnews.kSprache = " . (int)$_SESSION['kSprache']
                .$activeFilter, 1
    );
}

/**
 * @param int  $kNewsKategorie
 * @param bool $bActiveOnly
 * @return mixed
 */
function getCurrentNewsCategory($kNewsKategorie, $bActiveOnly = false)
{
    $activeFilter = $bActiveOnly ? ' AND tnewskategorie.nAktiv = 1 ' : '';

    return Shop::DB()->query(
        "SELECT tnewskategorie.cName, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription, tseo.cSeo
            FROM tnewskategorie
            LEFT JOIN tseo ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = " . (int)$kNewsKategorie . "
            AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            WHERE tnewskategorie.kNewsKategorie = " . (int)$kNewsKategorie
                .$activeFilter, 1
    );
}

/**
 * @param int $kNews
 * @return mixed
 */
function getNewsCategory($kNews)
{
    $cSQL                  = '';
    $oNewsKategorieKey_arr = Shop::DB()->query("SELECT kNewsKategorie FROM tnewskategorienews WHERE kNews = " . (int)$kNews, 2);

    if (is_array($oNewsKategorieKey_arr) && count($oNewsKategorieKey_arr) > 0) {
        $cSQL = '';
        foreach ($oNewsKategorieKey_arr as $i => $oNewsKategorieKey) {
            if ($oNewsKategorieKey->kNewsKategorie > 0) {
                if ($i > 0) {
                    $cSQL .= ', ' . (int)$oNewsKategorieKey->kNewsKategorie;
                } else {
                    $cSQL .= (int)$oNewsKategorieKey->kNewsKategorie;
                }
            }
        }
    }

    return Shop::DB()->query(
        "SELECT tnewskategorie.kNewsKategorie, tnewskategorie.kSprache, tnewskategorie.cName,
            tnewskategorie.cBeschreibung, tnewskategorie.cMetaTitle, tnewskategorie.cMetaDescription,
            tnewskategorie.nSort, tnewskategorie.nAktiv, tnewskategorie.dLetzteAktualisierung, tseo.cSeo,
            DATE_FORMAT(tnewskategorie.dLetzteAktualisierung, '%d.%m.%Y %H:%i') AS dLetzteAktualisierung_de
            FROM tnewskategorie
            LEFT JOIN tnewskategorienews ON tnewskategorienews.kNewsKategorie = tnewskategorie.kNewsKategorie
            LEFT JOIN tseo ON tseo.cKey = 'kNewsKategorie'
                AND tseo.kKey = tnewskategorie.kNewsKategorie
                AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            WHERE tnewskategorie.kSprache=" . (int)$_SESSION['kSprache'] . "
                AND tnewskategorienews.kNewsKategorie IN (" . $cSQL . ")
                AND tnewskategorie.nAktiv=1
            GROUP BY tnewskategorie.kNewsKategorie
            ORDER BY tnewskategorie.nSort DESC", 2
    );
}

/**
 * @param int      $kNews
 * @param int      $count
 * @param int|null $from
 * @param int|null $to
 * @return mixed
 */
function getNewsComments($kNews, $count, $from = null, $to = null)
{
    $cSQL = ' LIMIT ' . $count;
    if ($from !== null && $to !== null) {
        $cSQL = ' LIMIT ' . (int)$from . ', ' . (int)$to;
    }

    return Shop::DB()->query(
        "SELECT *, DATE_FORMAT(tnewskommentar.dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_de
            FROM tnewskommentar
            WHERE tnewskommentar.kNews = " . (int)$kNews . "
                AND tnewskommentar.nAktiv = 1
            ORDER BY tnewskommentar.dErstellt DESC" . $cSQL, 2
    );
}

/**
 * @param int $kNews
 * @return mixed
 */
function getCommentCount($kNews)
{
    return Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM tnewskommentar
            WHERE kNews = " . (int)$kNews . "
            AND nAktiv = 1", 1
    );
}

/**
 * @param int $kNewsMonatsUebersicht
 * @return mixed
 */
function getMonthOverview($kNewsMonatsUebersicht)
{
    return Shop::DB()->query(
        "SELECT tnewsmonatsuebersicht.*, tseo.cSeo
            FROM tnewsmonatsuebersicht
            LEFT JOIN tseo ON tseo.cKey = 'kNewsMonatsUebersicht'
                AND tseo.kKey = " . (int)$kNewsMonatsUebersicht . "
            AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            WHERE tnewsmonatsuebersicht.kNewsMonatsUebersicht = " . (int)$kNewsMonatsUebersicht, 1
    );
}

/**
 * @param object $oSQL
 * @return mixed
 */
function getNewsOverview($oSQL)
{
    return Shop::DB()->query(
        "SELECT tseo.cSeo, tnews.*, DATE_FORMAT(tnews.dGueltigVon, '%d.%m.%Y %H:%i') AS dErstellt_de, count(*) AS nAnzahl, count(DISTINCT(tnewskommentar.kNewsKommentar)) AS nNewsKommentarAnzahl
            FROM tnews
            LEFT JOIN tseo ON tseo.cKey = 'kNews'
                AND tseo.kKey = tnews.kNews
                AND tseo.kSprache = " . (int)$_SESSION['kSprache'] . "
            LEFT JOIN tnewskommentar ON tnewskommentar.kNews = tnews.kNews AND tnewskommentar.nAktiv=1
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv=1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' OR tnews.cKundengruppe LIKE '%;" . (int)$_SESSION['Kundengruppe']->kKundengruppe . ";%')
                AND tnews.kSprache = " . (int)$_SESSION['kSprache'] . "
                " . $oSQL->cDatumSQL . "
            GROUP BY tnews.kNews
            " . $oSQL->cSortSQL . $oSQL->cAnzahlSQL, 2
    );
}

/**
 * @param object $oSQL
 * @return mixed
 */
function getFullNewsOverview($oSQL)
{
    return Shop::DB()->query(
        "SELECT count(DISTINCT(tnews.kNews)) AS nAnzahl
            FROM tnews
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv=1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' OR tnews.cKundengruppe LIKE '%;" . (int)$_SESSION['Kundengruppe']->kKundengruppe . ";%')
                " . $oSQL->cDatumSQL . "
                AND tnews.kSprache = " . (int)$_SESSION['kSprache'], 1
    );
}

/**
 * @param object $oSQL
 * @return mixed
 */
function getNewsDateArray($oSQL)
{
    return Shop::DB()->query(
        "SELECT month(tnews.dGueltigVon) AS nMonat, year( tnews.dGueltigVon ) AS nJahr
            FROM tnews
            " . $oSQL->cNewsKatSQL . "
            WHERE tnews.nAktiv=1
                AND tnews.dGueltigVon <= now()
                AND (tnews.cKundengruppe LIKE '%;-1;%' OR tnews.cKundengruppe LIKE '%;" . (int)$_SESSION['Kundengruppe']->kKundengruppe . ";%')
                AND tnews.kSprache = " . (int)$_SESSION['kSprache'] . "
            GROUP BY nJahr, nMonat
            ORDER BY dGueltigVon DESC", 2
    );
}
