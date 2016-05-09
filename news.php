<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'news_inc.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'seite_inc.php';

if (Shop::$directEntry === true) {
    Shop::run();
    $cParameter_arr = Shop::getParameters();
    $NaviFilter     = Shop::buildNaviFilter($cParameter_arr);
    Shop::setPageType(PAGE_NEWS);
}
else {
    $cParameter_arr = array();
}

loeseHttps();
$cHinweis         = '';
$cFehler          = '';
$step             = 'news_uebersicht';
$cMetaTitle       = '';
$cMetaDescription = '';
$cMetaKeywords    = '';
$Einstellungen    = Shop::getSettings(array(
    CONF_GLOBAL,
    CONF_RSS,
    CONF_NEWS,
    CONF_KONTAKTFORMULAR
));

$linkHelper             = LinkHelper::getInstance();
$kLink                  = $linkHelper->getSpecialPageLinkKey(LINKTYP_NEWS);
$AktuelleKategorie      = new Kategorie(verifyGPCDataInteger('kategorie'));
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

$nAnzahlProSeite     = 2;
$nAktuelleSeite      = (isset(Shop::$kSeite) && Shop::$kSeite > 0) ? Shop::$kSeite : 1;
$oNewsUebersicht_arr = array();

if ($Einstellungen['news']['news_benutzen'] === 'Y') {
    // News Übersicht Filter
    if (!isset($_SESSION['NewsNaviFilter'])) {
        $_SESSION['NewsNaviFilter'] = new stdClass();
    }
    if (verifyGPCDataInteger('nSort') > 0) {
        $_SESSION['NewsNaviFilter']->nSort = verifyGPCDataInteger('nSort');
    } elseif (verifyGPCDataInteger('nSort') === -1) {
        $_SESSION['NewsNaviFilter']->nSort = -1;
    }
    if ($cParameter_arr['nAnzahl'] > 0) {
        $_SESSION['NewsNaviFilter']->nAnzahl = $cParameter_arr['nAnzahl'];
    } elseif ($cParameter_arr['nAnzahl'] === -1) {
        $_SESSION['NewsNaviFilter']->nAnzahl = -1;
    }
    if (strlen($cParameter_arr['cDatum']) > 0) {
        $_date                              = explode('-', $cParameter_arr['cDatum']);
        $_SESSION['NewsNaviFilter']->cDatum = (count($_date) > 1) ? StringHandler::filterXSS($cParameter_arr['cDatum']) : -1;
    } elseif (intval($cParameter_arr['cDatum']) === -1) {
        $_SESSION['NewsNaviFilter']->cDatum = -1;
    }
    if ($cParameter_arr['nNewsKat'] > 0) {
        $_SESSION['NewsNaviFilter']->nNewsKat = $cParameter_arr['nNewsKat'];
    } elseif ($cParameter_arr['nNewsKat'] === -1) {
        $_SESSION['NewsNaviFilter']->nNewsKat = -1;
    }

    $nAnzahlProSeite = (isset($_SESSION['NewsNaviFilter']->nAnzahl)) ? $_SESSION['NewsNaviFilter']->nAnzahl : null;
    $cacheID         = $smarty->getCacheID('blog/index.tpl', array('news' => $cParameter_arr));

    if ($smarty->isCached('blog/index.tpl', $cacheID)) {
        require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
        $smarty->display('blog/index.tpl', $cacheID);
    } else {
        if ($cParameter_arr['kNews'] > 0 || isset($kNews) && $kNews > 0) { // Detailansicht anzeigen
            Shop::$AktuelleSeite = 'NEWSDETAIL';
            $step                = 'news_detailansicht';
            if (!isset($kNews) || $kNews == 0) {
                $kNews = $cParameter_arr['kNews'];
            }
            $oNewsArchiv = getNewsArchive($kNews, true);

            if ($oNewsArchiv !== false) {
                if (isset($oNewsArchiv->kNews) && $oNewsArchiv->kNews > 0) {
                    $oNewsArchiv->cText = parseNewsText($oNewsArchiv->cText);
                    $smarty->assign('oNewsArchiv', $oNewsArchiv);
                }
                // Metas
                $cMetaTitle         = (isset($oNewsArchiv->cMetaTitle)) ? $oNewsArchiv->cMetaTitle : '';
                $cMetaDescription   = (isset($oNewsArchiv->cMetaDescription)) ? $oNewsArchiv->cMetaDescription : '';
                $cMetaKeywords      = (isset($oNewsArchiv->cMetaKeywords)) ? $oNewsArchiv->cMetaKeywords : '';
                $oNewsKategorie_arr = getNewsCategory($kNews);

                if (is_array($oNewsKategorie_arr) && count($oNewsKategorie_arr) > 0) {
                    foreach ($oNewsKategorie_arr as $j => $oNewsKategorie) {
                        $oNewsKategorie_arr[$j]->cURL = baueURL($oNewsKategorie, URLART_NEWSKATEGORIE);
                    }
                }
                $smarty->assign('R_LOGIN_NEWSCOMMENT', R_LOGIN_NEWSCOMMENT)
                    ->assign('oNewsKategorie_arr', $oNewsKategorie_arr);

                // Kommentar hinzufügen
                if (isset($_POST['kommentar_einfuegen']) && intval($_POST['kommentar_einfuegen']) > 0 &&
                    isset($Einstellungen['news']['news_kommentare_nutzen']) && $Einstellungen['news']['news_kommentare_nutzen'] === 'Y'
                ) {
                    // Plausi
                    $nPlausiValue_arr = pruefeKundenKommentar(
                        (isset($_POST['cKommentar'])) ? $_POST['cKommentar'] : '',
                        (isset($_POST['cName'])) ? $_POST['cName'] : null,
                        (isset($_POST['cEmail'])) ? $_POST['cEmail'] : null,
                        $kNews,
                        $Einstellungen
                    );

                    executeHook(HOOK_NEWS_PAGE_NEWSKOMMENTAR_PLAUSI);

                    if ($Einstellungen['news']['news_kommentare_eingeloggt'] === 'Y' && $_SESSION['Kunde']->kKunde > 0) {
                        if (is_array($nPlausiValue_arr) && count($nPlausiValue_arr) === 0) {
                            $oNewsKommentar             = new stdClass();
                            $oNewsKommentar->kNews      = (int)$_POST['kNews'];
                            $oNewsKommentar->kKunde     = $_SESSION['Kunde']->kKunde;
                            $oNewsKommentar->nAktiv     = ($Einstellungen['news']['news_kommentare_freischalten'] === 'Y') ? 0 : 1;
                            $oNewsKommentar->cName      = $_SESSION['Kunde']->cVorname . ' ' . substr($_SESSION['Kunde']->cNachname, 0, 1) . '.';
                            $oNewsKommentar->cEmail     = $_SESSION['Kunde']->cMail;
                            $oNewsKommentar->cKommentar = StringHandler::htmlentities(StringHandler::filterXSS($_POST['cKommentar']));
                            $oNewsKommentar->dErstellt  = 'now()';

                            executeHook(HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, array('comment' => &$oNewsKommentar));

                            Shop::DB()->insert('tnewskommentar', $oNewsKommentar);

                            if ($Einstellungen['news']['news_kommentare_freischalten'] === 'Y') {
                                $cHinweis .= Shop::Lang()->get('newscommentAddactivate', 'messages') . '<br>';
                            } else {
                                $cHinweis .= Shop::Lang()->get('newscommentAdd', 'messages') . '<br>';
                            }
                        } else {
                            $cFehler .= gibNewskommentarFehler($nPlausiValue_arr);
                            $smarty->assign('nPlausiValue_arr', $nPlausiValue_arr)
                                ->assign('cPostVar_arr', StringHandler::filterXSS($_POST));
                        }
                    } elseif ($Einstellungen['news']['news_kommentare_eingeloggt'] === 'N') {
                        if (is_array($nPlausiValue_arr) && count($nPlausiValue_arr) === 0) {
                            $cEmail = (isset($_POST['cEmail'])) ? $_POST['cEmail'] : null;
                            if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                                $cEmail = $_SESSION['Kunde']->cMail;
                            }
                            $oNewsKommentar         = new stdClass();
                            $oNewsKommentar->kNews  = (int)$_POST['kNews'];
                            $oNewsKommentar->kKunde = (isset($_SESSION['Kunde']->kKunde)) ? $_SESSION['Kunde']->kKunde : 0;
                            $oNewsKommentar->nAktiv = ($Einstellungen['news']['news_kommentare_freischalten'] === 'Y') ? 0 : 1;

                            if (isset($_SESSION['Kunde']->kKunde) && $_SESSION['Kunde']->kKunde > 0) {
                                $cName  = $_SESSION['Kunde']->cVorname . ' ' . substr($_SESSION['Kunde']->cNachname, 0, 1) . '.';
                                $cEmail = $_SESSION['Kunde']->cMail;
                            } else {
                                $cName  = StringHandler::filterXSS($_POST['cName']);
                                $cEmail = StringHandler::filterXSS($_POST['cEmail']);
                            }

                            $oNewsKommentar->cName      = $cName;
                            $oNewsKommentar->cEmail     = $cEmail;
                            $oNewsKommentar->cKommentar = StringHandler::htmlentities(StringHandler::filterXSS($_POST['cKommentar']));
                            $oNewsKommentar->dErstellt  = 'now()';

                            executeHook(HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, array('comment' => &$oNewsKommentar));

                            Shop::DB()->insert('tnewskommentar', $oNewsKommentar);

                            if ($Einstellungen['news']['news_kommentare_freischalten'] === 'Y') {
                                $cHinweis .= Shop::Lang()->get('newscommentAddactivate', 'messages') . '<br />';
                            } else {
                                $cHinweis .= Shop::Lang()->get('newscommentAdd', 'messages') . '<br />';
                            }
                        } else {
                            $cFehler .= gibNewskommentarFehler($nPlausiValue_arr);
                            $smarty->assign('nPlausiValue_arr', $nPlausiValue_arr)
                                ->assign('cPostVar_arr', StringHandler::filterXSS($_POST));
                        }
                    }
                }
                $oNewsKommentarAnzahl = getCommentCount($kNews);
                $oNewsKommentar_arr   = array();
                if (isset($oNewsKommentarAnzahl->nAnzahl) && $oNewsKommentarAnzahl->nAnzahl > 0) {
                    // Baue Blätter Navigation
                    $nAnzahlProSeite = 10;
                    $from            = null;
                    $to              = null;
                    if ((int)$Einstellungen['news']['news_kommentare_anzahlproseite'] > 0) {
                        $nAnzahlProSeite = (int)$Einstellungen['news']['news_kommentare_anzahlproseite'];
                        $from            = (($nAktuelleSeite - 1) * $nAnzahlProSeite);
                        $to              = $nAnzahlProSeite;
                    }
                    $smarty->assign('oBlaetterNavi', baueBlaetterNavi($nAktuelleSeite, $oNewsKommentarAnzahl->nAnzahl, $nAnzahlProSeite));
                    $oNewsKommentar_arr = getNewsComments($kNews, $nAnzahlProSeite, $from, $to);
                }
                $smarty->assign('oNewsKommentar_arr', $oNewsKommentar_arr);
                // Canonical
                if (strpos(baueURL($oNewsArchiv, URLART_NEWS), '.php') === false || !SHOP_SEO) {
                    $cCanonicalURL = Shop::getURL() . '/' . baueURL($oNewsArchiv, URLART_NEWS);
                }
                $smarty->assign('Navigation', createNavigation(Shop::$AktuelleSeite, 0, 0,
                    (isset($oNewsArchiv->cBetreff) ? $oNewsArchiv->cBetreff : Shop::Lang()->get('news', 'breadcrumb')),
                    baueURL($oNewsArchiv, URLART_NEWS)));

                executeHook(HOOK_NEWS_PAGE_DETAILANSICHT);
            } else {
                Shop::$AktuelleSeite = 'NEWS';
                $smarty->assign('cNewsErr', 1);
                baueNewsKruemel($smarty, Shop::$AktuelleSeite, $cCanonicalURL);
            }
        } else { // Beitragsübersicht anzeigen
            if ($cParameter_arr['kNewsKategorie'] > 0) { // NewsKategorie Übersicht
                Shop::$AktuelleSeite = 'NEWSKATEGORIE';
                $kNewsKategorie = (int)$cParameter_arr['kNewsKategorie'];
                $oNewsKategorie = getCurrentNewsCategory($kNewsKategorie, true);

                if (!isset($oNewsKategorie) || !is_object($oNewsKategorie)) {
                    Shop::$AktuelleSeite = 'NEWS';
                    $cFehler .= Shop::Lang()->get('newsRestricted', 'news');
                    $_SESSION['NewsNaviFilter']->nNewsKat = -1;
                    baueNewsKruemel($smarty, Shop::$AktuelleSeite, $cCanonicalURL);
                } else {
                    if (strlen($oNewsKategorie->cMetaTitle) > 0) {
                        $cMetaTitle = $oNewsKategorie->cMetaTitle;
                    }
                    if (strlen($oNewsKategorie->cMetaDescription) > 0) {
                        $cMetaDescription = $oNewsKategorie->cMetaDescription;
                    }
                    // Canonical
                    if (isset($oNewsKategorie->cSeo) && SHOP_SEO) {
                        $cCanonicalURL = Shop::getURL() . '/' . $oNewsKategorie->cSeo;
                        $smarty->assign('Navigation', createNavigation(Shop::$AktuelleSeite, 0, 0, $oNewsKategorie->cName, $cCanonicalURL));
                    } elseif (!SHOP_SEO) {
                        $cCanonicalURL = Shop::getURL() . '/news.php?nk=' . $kNewsKategorie;
                        $smarty->assign('Navigation', createNavigation(Shop::$AktuelleSeite, 0, 0, $oNewsKategorie->cName, $cCanonicalURL));
                    }

                    if (!isset($_SESSION['NewsNaviFilter'])) {
                        $_SESSION['NewsNaviFilter'] = new stdClass();
                    }
                    $_SESSION['NewsNaviFilter']->nNewsKat = $kNewsKategorie;
                    $_SESSION['NewsNaviFilter']->cDatum = -1;
                }
            } elseif ($cParameter_arr['kNewsMonatsUebersicht'] > 0) { // Monatsuebersicht
                Shop::$AktuelleSeite = 'NEWSMONAT';
                $kNewsMonatsUebersicht = (int)$cParameter_arr['kNewsMonatsUebersicht'];
                $oNewsMonatsUebersicht = getMonthOverview($kNewsMonatsUebersicht);

                if (isset($oNewsMonatsUebersicht->cSeo) && SHOP_SEO) {
                    $cCanonicalURL = Shop::getURL() . '/' . $oNewsMonatsUebersicht->cSeo;
                    $smarty->assign('Navigation', createNavigation(Shop::$AktuelleSeite, 0, 0, $oNewsMonatsUebersicht->cName, $cCanonicalURL));
                } elseif (!SHOP_SEO) {
                    $cCanonicalURL = Shop::getURL() . '/news.php?nm=' . $oNewsMonatsUebersicht->kNewsMonatsUebersicht;
                    $smarty->assign('Navigation', createNavigation(Shop::$AktuelleSeite, 0, 0, $oNewsMonatsUebersicht->cName, $cCanonicalURL));
                }
                if (!isset($_SESSION['NewsNaviFilter'])) {
                    $_SESSION['NewsNaviFilter'] = new stdClass();
                }
                $_SESSION['NewsNaviFilter']->cDatum   = (int)$oNewsMonatsUebersicht->nMonat . '-' . (int)$oNewsMonatsUebersicht->nJahr;
                $_SESSION['NewsNaviFilter']->nNewsKat = -1;
            } else { // Startseite News Übersicht
                Shop::$AktuelleSeite = 'NEWS';
                baueNewsKruemel($smarty, Shop::$AktuelleSeite, $cCanonicalURL);
            }

            if (!isset($_SESSION['NewsNaviFilter'])) {
                $_SESSION['NewsNaviFilter'] = new stdClass();
            }
            if (!isset($_SESSION['NewsNaviFilter']->nSort)) {
                $_SESSION['NewsNaviFilter']->nSort = -1;
            }
            if (!isset($_SESSION['NewsNaviFilter']->nAnzahl)) {
                $_SESSION['NewsNaviFilter']->nAnzahl = -1;
            }
            if (!isset($_SESSION['NewsNaviFilter']->cDatum)) {
                $_SESSION['NewsNaviFilter']->cDatum = -1;
            }
            if (!isset($_SESSION['NewsNaviFilter']->nNewsKat)) {
                $_SESSION['NewsNaviFilter']->nNewsKat = -1;
            }

            // Baut den NewsNaviFilter SQL
            $oSQL                = baueFilterSQL($nAktuelleSeite, true);
            $oNewsUebersicht_arr = getNewsOverview($oSQL);
            $oNewsUebersichtAll  = getFullNewsOverview($oSQL);
            $oDatum_arr          = getNewsDateArray($oSQL);
            $cKeywords           = '';
            if (is_array($oNewsUebersicht_arr) && count($oNewsUebersicht_arr) > 0) {
                foreach ($oNewsUebersicht_arr as $i => $oNewsUebersicht) {
                    if ($i > 0) {
                        $cKeywords .= ', ' . $oNewsUebersicht->cBetreff;
                    } else {
                        $cKeywords .= $oNewsUebersicht->cBetreff;
                    }
                    $oNewsUebersicht_arr[$i]->cText    = parseNewsText($oNewsUebersicht_arr[$i]->cText);
                    $oNewsUebersicht_arr[$i]->cURL     = baueURL($oNewsUebersicht, URLART_NEWS);
                    $oNewsUebersicht_arr[$i]->cMehrURL = '<a class="news-more-link" href="' . $oNewsUebersicht_arr[$i]->cURL . '">' . Shop::Lang()->get('moreLink', 'news') . '</a>';
                }
            }
            $cMetaTitle       = (strlen($cMetaDescription) < 1) ? baueNewsMetaTitle($_SESSION['NewsNaviFilter'], $oNewsUebersicht_arr) : $cMetaTitle;
            $cMetaDescription = (strlen($cMetaDescription) < 1) ? baueNewsMetaDescription($_SESSION['NewsNaviFilter'], $oNewsUebersicht_arr) : $cMetaDescription;
            $cMetaKeywords    = (strlen($cMetaKeywords) < 1) ? baueNewsMetaKeywords($_SESSION['NewsNaviFilter'], $oNewsUebersicht_arr) : $cMetaKeywords;
            if ($nAnzahlProSeite > 0) {
                $oBlaetterNavi = baueBlaetterNavi($nAktuelleSeite, $oNewsUebersichtAll->nAnzahl, $nAnzahlProSeite);
                $smarty->assign('oBlaetterNavi', $oBlaetterNavi);
            }

            $smarty->assign('oNewsUebersicht_arr', $oNewsUebersicht_arr)
                ->assign('oNewsKategorie_arr', holeNewsKategorien($oSQL->cDatumSQL, true))
                ->assign('oDatum_arr', baueDatum($oDatum_arr))
                ->assign('nAnzahl', $_SESSION['NewsNaviFilter']->nAnzahl)
                ->assign('nSort', $_SESSION['NewsNaviFilter']->nSort)
                ->assign('cDatum', $_SESSION['NewsNaviFilter']->cDatum)
                ->assign('nNewsKat', $_SESSION['NewsNaviFilter']->nNewsKat);

            if (!isset($oNewsUebersicht_arr) || count($oNewsUebersicht_arr) === 0) {
                $smarty->assign('noarchiv', 1);
                $_SESSION['NewsNaviFilter']->nNewsKat = -1;
                $_SESSION['NewsNaviFilter']->cDatum = -1;
            }

            executeHook(HOOK_NEWS_PAGE_NEWSUEBERSICHT);
        }

        $smarty->assign('Einstellungen', $Einstellungen)
               ->assign('hinweis', $cHinweis)
               ->assign('fehler', $cFehler)
               ->assign('step', $step)
               ->assign('SID', (isset($sid)) ? $sid : null)
               ->assign('code_news', generiereCaptchaCode((isset($Einstellungen['news']['news_sicherheitscode'])) ? $Einstellungen['news']['news_sicherheitscode'] : 'N'));

        require_once PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';
        $smarty->assign('meta_title', $cMetaTitle)
               ->assign('meta_description', $cMetaDescription)
               ->assign('meta_keywords', $cMetaKeywords)
               ->display('blog/index.tpl', $cacheID);
    }
    require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
} else {
    $oLink                   = Shop::DB()->query("SELECT kLink FROM tlink WHERE nLinkart = " . LINKTYP_404, 1);
    $bFileNotFound           = true;
    Shop::$kLink             = $oLink->kLink;
    Shop::$bFileNotFound     = true;
    Shop::$is404             = true;
    $cParameter_arr['is404'] = true;
    require_once PFAD_ROOT . 'seite.php';
}
