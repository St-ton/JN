<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'news_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

if (Shop::$directEntry === true) {
    $NaviFilter     = Shop::run();
    $cParameter_arr = Shop::getParameters();
    Shop::setPageType(PAGE_NEWS);
} else {
    $cParameter_arr = [];
}
$cHinweis               = '';
$cFehler                = '';
$step                   = 'news_uebersicht';
$cMetaTitle             = '';
$cMetaDescription       = '';
$cMetaKeywords          = '';
$AktuelleSeite          = 'NEWS';
$Einstellungen          = Shop::getSettings([
    CONF_GLOBAL,
    CONF_RSS,
    CONF_NEWS,
    CONF_KONTAKTFORMULAR,
    CONF_METAANGABEN
]);
$nAktuelleSeite         = (Shop::$kSeite !== null && Shop::$kSeite > 0) ? Shop::$kSeite : 1;
$oNewsUebersicht_arr    = [];
$linkHelper             = Shop::Container()->getLinkService();
$kLink                  = $linkHelper->getSpecialPageLinkKey(LINKTYP_NEWS);
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$cUploadVerzeichnis     = PFAD_ROOT . PFAD_NEWSBILDER;
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

if ($Einstellungen['news']['news_benutzen'] === 'Y') {
    // News Übersicht Filter
    if (!isset($_SESSION['NewsNaviFilter'])) {
        $_SESSION['NewsNaviFilter'] = new stdClass();
    }
    if (RequestHelper::verifyGPCDataInt('nSort') > 0) {
        $_SESSION['NewsNaviFilter']->nSort = RequestHelper::verifyGPCDataInt('nSort');
    } elseif (RequestHelper::verifyGPCDataInt('nSort') === -1) {
        $_SESSION['NewsNaviFilter']->nSort = -1;
    }
    if (strlen($cParameter_arr['cDatum']) > 0) {
        $_date                              = explode('-', $cParameter_arr['cDatum']);
        $_SESSION['NewsNaviFilter']->cDatum = (count($_date) > 1)
            ? StringHandler::filterXSS($cParameter_arr['cDatum'])
            : -1;
    } elseif ((int)$cParameter_arr['cDatum'] === -1) {
        $_SESSION['NewsNaviFilter']->cDatum = -1;
    }
    if ($cParameter_arr['nNewsKat'] > 0) {
        $_SESSION['NewsNaviFilter']->nNewsKat = $cParameter_arr['nNewsKat'];
    } elseif ($cParameter_arr['nNewsKat'] === -1) {
        $_SESSION['NewsNaviFilter']->nNewsKat = -1;
    }
    if ($cParameter_arr['kNews'] > 0 || (isset($kNews) && $kNews > 0)) {
        // Detailansicht anzeigen
        Shop::$AktuelleSeite = 'NEWSDETAIL';
        $AktuelleSeite       = 'NEWSDETAIL';
        $step                = 'news_detailansicht';
        if (empty($kNews)) {
            $kNews = $cParameter_arr['kNews'];
        }
        $oNewsArchiv = getNewsArchive($kNews, true);

        if ($oNewsArchiv !== false) {
            if (isset($oNewsArchiv->kNews) && $oNewsArchiv->kNews > 0) {
                $oNewsArchiv->cText = StringHandler::parseNewsText($oNewsArchiv->cText);
                $oNewsArchiv->oDatei_arr = [];
                if (is_dir($cUploadVerzeichnis . $oNewsArchiv->kNews)) {
                    $oNewsArchiv->oDatei_arr     = holeNewsBilder($oNewsArchiv->kNews, $cUploadVerzeichnis);
                }
                Shop::Smarty()->assign('oNewsArchiv', $oNewsArchiv);
            }
            // Metas
            $cMetaTitle         = $oNewsArchiv->cMetaTitle ?? '';
            $cMetaDescription   = $oNewsArchiv->cMetaDescription ?? '';
            $cMetaKeywords      = $oNewsArchiv->cMetaKeywords ?? '';
            $oNewsKategorie_arr = getNewsCategory($kNews);
            foreach ($oNewsKategorie_arr as $j => $oNewsKategorie) {
                $oNewsKategorie_arr[$j]->cURL     = baueURL($oNewsKategorie, URLART_NEWSKATEGORIE);
                $oNewsKategorie_arr[$j]->cURLFull = baueURL($oNewsKategorie, URLART_NEWSKATEGORIE, 0, false, true);
            }
            Shop::Smarty()->assign('R_LOGIN_NEWSCOMMENT', R_LOGIN_NEWSCOMMENT)
                ->assign('oNewsKategorie_arr', $oNewsKategorie_arr);

            // Kommentar hinzufügen
            if (isset($_POST['kommentar_einfuegen'], $Einstellungen['news']['news_kommentare_nutzen'])
                && (int)$_POST['kommentar_einfuegen'] > 0
                && $Einstellungen['news']['news_kommentare_nutzen'] === 'Y'
            ) {
                // Plausi
                $nPlausiValue_arr = pruefeKundenKommentar(
                    $_POST['cKommentar'] ?? '',
                    $_POST['cName'] ?? null,
                    $_POST['cEmail'] ?? null,
                    $kNews,
                    $Einstellungen
                );

                executeHook(HOOK_NEWS_PAGE_NEWSKOMMENTAR_PLAUSI);

                if ($Einstellungen['news']['news_kommentare_eingeloggt'] === 'Y' && !empty($_SESSION['Kunde']->kKunde)) {
                    if (is_array($nPlausiValue_arr) && count($nPlausiValue_arr) === 0) {
                        $oNewsKommentar             = new stdClass();
                        $oNewsKommentar->kNews      = (int)$_POST['kNews'];
                        $oNewsKommentar->kKunde     = (int)$_SESSION['Kunde']->kKunde;
                        $oNewsKommentar->nAktiv     = ($Einstellungen['news']['news_kommentare_freischalten'] === 'Y')
                            ? 0
                            : 1;
                        $oNewsKommentar->cName      = $_SESSION['Kunde']->cVorname . ' ' .
                            substr($_SESSION['Kunde']->cNachname, 0, 1) . '.';
                        $oNewsKommentar->cEmail     = $_SESSION['Kunde']->cMail;
                        $oNewsKommentar->cKommentar = StringHandler::htmlentities(
                            StringHandler::filterXSS($_POST['cKommentar'])
                        );
                        $oNewsKommentar->dErstellt  = 'now()';

                        executeHook(HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, ['comment' => &$oNewsKommentar]);

                        Shop::Container()->getDB()->insert('tnewskommentar', $oNewsKommentar);

                        if ($Einstellungen['news']['news_kommentare_freischalten'] === 'Y') {
                            $cHinweis .= Shop::Lang()->get('newscommentAddactivate', 'messages') . '<br>';
                        } else {
                            $cHinweis .= Shop::Lang()->get('newscommentAdd', 'messages') . '<br>';
                        }
                    } else {
                        $cFehler .= gibNewskommentarFehler($nPlausiValue_arr);
                        Shop::Smarty()->assign('nPlausiValue_arr', $nPlausiValue_arr)
                            ->assign('cPostVar_arr', StringHandler::filterXSS($_POST));
                    }
                } elseif ($Einstellungen['news']['news_kommentare_eingeloggt'] === 'N') {
                    if (is_array($nPlausiValue_arr) && count($nPlausiValue_arr) === 0) {
                        $cEmail = $_POST['cEmail'] ?? null;
                        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                            $cEmail = $_SESSION['Kunde']->cMail;
                        }
                        $oNewsKommentar         = new stdClass();
                        $oNewsKommentar->kNews  = (int)$_POST['kNews'];
                        $oNewsKommentar->kKunde = $_SESSION['Kunde']->kKunde ?? 0;
                        $oNewsKommentar->nAktiv = $Einstellungen['news']['news_kommentare_freischalten'] === 'Y'
                            ? 0
                            : 1;

                        if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                            $cName  = $_SESSION['Kunde']->cVorname . ' ' .
                                substr($_SESSION['Kunde']->cNachname, 0, 1) . '.';
                            $cEmail = $_SESSION['Kunde']->cMail;
                        } else {
                            $cName  = StringHandler::filterXSS($_POST['cName']);
                            $cEmail = StringHandler::filterXSS($_POST['cEmail']);
                        }

                        $oNewsKommentar->cName      = $cName;
                        $oNewsKommentar->cEmail     = $cEmail;
                        $oNewsKommentar->cKommentar = StringHandler::htmlentities(
                            StringHandler::filterXSS($_POST['cKommentar'])
                        );
                        $oNewsKommentar->dErstellt  = 'now()';

                        executeHook(HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, ['comment' => &$oNewsKommentar]);

                        Shop::Container()->getDB()->insert('tnewskommentar', $oNewsKommentar);

                        if ($Einstellungen['news']['news_kommentare_freischalten'] === 'Y') {
                            $cHinweis .= Shop::Lang()->get('newscommentAddactivate', 'messages') . '<br />';
                        } else {
                            $cHinweis .= Shop::Lang()->get('newscommentAdd', 'messages') . '<br />';
                        }
                    } else {
                        $cFehler .= gibNewskommentarFehler($nPlausiValue_arr);
                        Shop::Smarty()->assign('nPlausiValue_arr', $nPlausiValue_arr)
                            ->assign('cPostVar_arr', StringHandler::filterXSS($_POST));
                    }
                }
            }

            $oNewsKommentarAnzahl = getCommentCount($kNews);

            if ((int)$Einstellungen['news']['news_kommentare_anzahlproseite'] > 0) {
                $nCountPerPagePref   = (int)$Einstellungen['news']['news_kommentare_anzahlproseite'];
                $itemsPerPageOptions = [$nCountPerPagePref, $nCountPerPagePref * 2, $nCountPerPagePref * 5];
            } else {
                $itemsPerPageOptions = [10, 20, 50];
            }

            $oPagiComments = (new Pagination('comments'))
                ->setItemsPerPageOptions($itemsPerPageOptions)
                ->setItemCount($oNewsKommentarAnzahl->nAnzahl)
                ->assemble();

            $oNewsKommentar_arr = getNewsComments($kNews, $oPagiComments->getLimitSQL());

            Shop::Smarty()->assign('oNewsKommentar_arr', $oNewsKommentar_arr)
                ->assign('oPagiComments', $oPagiComments);
            // Canonical
            if (strpos(baueURL($oNewsArchiv, URLART_NEWS), '.php') === false) {
                $cCanonicalURL = Shop::getURL() . '/' . baueURL($oNewsArchiv, URLART_NEWS);
            }
            Shop::Smarty()->assign('Brotnavi', createNavigation(
                Shop::$AktuelleSeite,
                0,
                0,
                $oNewsArchiv->cBetreff ?? Shop::Lang()->get('news', 'breadcrumb'),
                baueURL($oNewsArchiv, URLART_NEWS))
            );

            executeHook(HOOK_NEWS_PAGE_DETAILANSICHT);
        } else {
            Shop::$AktuelleSeite = 'NEWS';
            $AktuelleSeite       = 'NEWS';
            Shop::Smarty()->assign('cNewsErr', 1);
            baueNewsKruemel(Shop::Smarty(), Shop::$AktuelleSeite, $cCanonicalURL);
        }
    } else { // Beitragsübersicht anzeigen
        if ($cParameter_arr['kNewsKategorie'] > 0) { // NewsKategorie Übersicht
            Shop::$AktuelleSeite = 'NEWSKATEGORIE';
            $AktuelleSeite       = 'NEWSKATEGORIE';
            $kNewsKategorie      = (int)$cParameter_arr['kNewsKategorie'];
            $oNewsKategorie      = getCurrentNewsCategory($kNewsKategorie, true);

            if (!isset($oNewsKategorie) || !is_object($oNewsKategorie)) {
                Shop::$AktuelleSeite = 'NEWS';
                $cFehler .= Shop::Lang()->get('newsRestricted', 'news');
                $_SESSION['NewsNaviFilter']->nNewsKat = -1;
                baueNewsKruemel(Shop::Smarty(), Shop::$AktuelleSeite, $cCanonicalURL);
            } else {
                if (strlen($oNewsKategorie->cMetaTitle) > 0) {
                    $cMetaTitle = $oNewsKategorie->cMetaTitle;
                }
                if (strlen($oNewsKategorie->cMetaDescription) > 0) {
                    $cMetaDescription = $oNewsKategorie->cMetaDescription;
                }
                // Canonical
                if (isset($oNewsKategorie->cSeo)) {
                    $cCanonicalURL = Shop::getURL() . '/' . $oNewsKategorie->cSeo;
                    Shop::Smarty()->assign('Brotnavi', createNavigation(
                            Shop::$AktuelleSeite,
                            0,
                            0,
                            $oNewsKategorie->cName,
                            $cCanonicalURL
                        )
                    );
                }
                if (!isset($_SESSION['NewsNaviFilter'])) {
                    $_SESSION['NewsNaviFilter'] = new stdClass();
                }
                $_SESSION['NewsNaviFilter']->nNewsKat = $kNewsKategorie;
                $_SESSION['NewsNaviFilter']->cDatum   = -1;
            }
        } elseif ($cParameter_arr['kNewsMonatsUebersicht'] > 0) { // Monatsuebersicht
            Shop::$AktuelleSeite   = 'NEWSMONAT';
            $AktuelleSeite         = 'NEWSMONAT';
            $kNewsMonatsUebersicht = (int)$cParameter_arr['kNewsMonatsUebersicht'];
            $oNewsMonatsUebersicht = getMonthOverview($kNewsMonatsUebersicht);

            if (isset($oNewsMonatsUebersicht->cSeo)) {
                $cCanonicalURL = Shop::getURL() . '/' . $oNewsMonatsUebersicht->cSeo;
                Shop::Smarty()->assign('Brotnavi', createNavigation(
                        Shop::$AktuelleSeite,
                        0,
                        0,
                        $oNewsMonatsUebersicht->cName,
                        $cCanonicalURL
                    )
                );
            }
            if (!isset($_SESSION['NewsNaviFilter'])) {
                $_SESSION['NewsNaviFilter'] = new stdClass();
            }
            $_SESSION['NewsNaviFilter']->cDatum   = (int)$oNewsMonatsUebersicht->nMonat . '-' .
                (int)$oNewsMonatsUebersicht->nJahr;
            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
        } else { // Startseite News Übersicht
            Shop::$AktuelleSeite = 'NEWS';
            $AktuelleSeite       = 'NEWS';
            baueNewsKruemel(Shop::Smarty(), Shop::$AktuelleSeite, $cCanonicalURL);
        }

        if (!isset($_SESSION['NewsNaviFilter'])) {
            $_SESSION['NewsNaviFilter'] = new stdClass();
        }
        if (!isset($_SESSION['NewsNaviFilter']->nSort)) {
            $_SESSION['NewsNaviFilter']->nSort = -1;
        }
        if (!isset($_SESSION['NewsNaviFilter']->cDatum)) {
            $_SESSION['NewsNaviFilter']->cDatum = -1;
        }
        if (!isset($_SESSION['NewsNaviFilter']->nNewsKat)) {
            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
        }

        // Baut den NewsNaviFilter SQL
        $oSQL = baueFilterSQL(true);
        // News total count
        $oNewsUebersichtAll = getFullNewsOverview($oSQL);
        // Pagination
        $newsCountShow = isset($Einstellungen['news']['news_anzahl_uebersicht'])
                && (int)$Einstellungen['news']['news_anzahl_uebersicht'] > 0
            ? (int)$Einstellungen['news']['news_anzahl_uebersicht']
            : 10;
        $oPagination   = (new Pagination())
            ->setItemsPerPageOptions([$newsCountShow, $newsCountShow * 2, $newsCountShow * 5])
            ->setDefaultItemsPerPage(0)
            ->setItemCount($oNewsUebersichtAll->nAnzahl)
            ->assemble();
        // Get filtered news of current page
        $oNewsUebersicht_arr = getNewsOverview($oSQL, $oPagination->getLimitSQL());
        $oDatum_arr          = getNewsDateArray($oSQL);
        $cKeywords           = '';
        $shopURL             = Shop::getURL() . '/';
        if (is_array($oNewsUebersicht_arr) && count($oNewsUebersicht_arr) > 0) {
            foreach ($oNewsUebersicht_arr as $i => $oNewsUebersicht) {
                if ($i > 0) {
                    $cKeywords .= ', ' . $oNewsUebersicht->cBetreff;
                } else {
                    $cKeywords .= $oNewsUebersicht->cBetreff;
                }
                $oNewsUebersicht_arr[$i]->cPreviewImageFull = empty($oNewsUebersicht_arr[$i]->cPreviewImage)
                    ? ''
                    : $shopURL . $oNewsUebersicht_arr[$i]->cPreviewImage;
                if (is_dir($cUploadVerzeichnis . $oNewsUebersicht->kNews)) {
                    $oNewsUebersicht_arr[$i]->oDatei_arr = holeNewsBilder($oNewsUebersicht->kNews, $cUploadVerzeichnis);
                }
                $oNewsUebersicht_arr[$i]->cText             = StringHandler::parseNewsText($oNewsUebersicht_arr[$i]->cText);
                $oNewsUebersicht_arr[$i]->cURL              = baueURL($oNewsUebersicht, URLART_NEWS);
                $oNewsUebersicht_arr[$i]->cURLFull          = $shopURL . $oNewsUebersicht_arr[$i]->cURL;
                $oNewsUebersicht_arr[$i]->cMehrURL          = '<a href="' . $oNewsUebersicht_arr[$i]->cURL . '">' .
                    Shop::Lang()->get('moreLink', 'news') .
                    '</a>';
                $oNewsUebersicht_arr[$i]->cMehrURLFull      = '<a href="' . $oNewsUebersicht_arr[$i]->cURLFull . '">' .
                    Shop::Lang()->get('moreLink', 'news') .
                    '</a>';
            }
        }
        $cMetaTitle       = strlen($cMetaDescription) < 1
            ? Shop::Lang()->get('news', 'news') . ' ' .
                Shop::Lang()->get('from', 'global') . ' ' . $Einstellungen['global']['global_shopname']
            : $cMetaTitle;
        $cMetaDescription = strlen($cMetaDescription) < 1
            ? Shop::Lang()->get('newsMetaDesc', 'news')
            : $cMetaDescription;
        $cMetaKeywords    = strlen($cMetaKeywords) < 1
            ? baueNewsMetaKeywords($_SESSION['NewsNaviFilter'], $oNewsUebersicht_arr)
            : $cMetaKeywords;

        Shop::Smarty()->assign('oNewsUebersicht_arr', $oNewsUebersicht_arr)
            ->assign('oNewsKategorie_arr', holeNewsKategorien($oSQL->cDatumSQL, true))
            ->assign('oDatum_arr', baueDatum($oDatum_arr))
            ->assign('nSort', $_SESSION['NewsNaviFilter']->nSort)
            ->assign('cDatum', $_SESSION['NewsNaviFilter']->cDatum)
            ->assign('nNewsKat', $_SESSION['NewsNaviFilter']->nNewsKat)
            ->assign('oPagination', $oPagination);

        if (!isset($oNewsUebersicht_arr) || count($oNewsUebersicht_arr) === 0) {
            Shop::Smarty()->assign('noarchiv', 1);
            $_SESSION['NewsNaviFilter']->nNewsKat = -1;
            $_SESSION['NewsNaviFilter']->cDatum   = -1;
        }

        executeHook(HOOK_NEWS_PAGE_NEWSUEBERSICHT);
    }

    $cMetaTitle = \Filter\Metadata::prepareMeta($cMetaTitle, null, (int)$Einstellungen['metaangaben']['global_meta_maxlaenge_title']);

    Shop::Smarty()->assign('hinweis', $cHinweis)
        ->assign('fehler', $cFehler)
        ->assign('step', $step)
        ->assign('code_news', false);

    require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
    Shop::Smarty()->assign('meta_title', $cMetaTitle)
        ->assign('meta_description', $cMetaDescription)
        ->assign('meta_keywords', $cMetaKeywords)
        ->display('blog/index.tpl');
    require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
} else {
    $oLink                   = Shop::Container()->getDB()->select('tlink', 'nLinkart', LINKTYP_404);
    $bFileNotFound           = true;
    Shop::$kLink             = (int)$oLink->kLink;
    Shop::$bFileNotFound     = true;
    Shop::$is404             = true;
    $cParameter_arr['is404'] = true;
    require_once PFAD_ROOT . 'seite.php';
}
