<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *
 * @global JTLSmarty $smarty
 */
require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_DBES . 'seo.php';

$oAccount->permission('CONTENT_NEWS_SYSTEM_VIEW', true, true);
/** @global JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'news_inc.php';

$Einstellungen         = Shop::getSettings([CONF_NEWS]);
$cHinweis              = '';
$cFehler               = '';
$step                  = 'news_uebersicht';
$cUploadVerzeichnis    = PFAD_ROOT . PFAD_NEWSBILDER;
$cUploadVerzeichnisKat = PFAD_ROOT . PFAD_NEWSKATEGORIEBILDER;
$newsCategory_arr      = [];
$continueWith          = false;
$db                    = Shop::Container()->getDB();
$author                = ContentAuthor::getInstance();
$controller            = new \News\Admin\Controller($db, $smarty);
$languages             = Sprache::getAllLanguages();
setzeSprache();
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $backTab = RequestHelper::verifyGPDataString('tab');
    $smarty->assign('cTab', $backTab);

    switch ($backTab) {
        case 'inaktiv':
            if (RequestHelper::verifyGPCDataInt('s1') > 1) {
                $smarty->assign('cBackPage', 'tab=inaktiv&s1=' . RequestHelper::verifyGPCDataInt('s1'))
                       ->assign('cSeite', RequestHelper::verifyGPCDataInt('s1'));
            }
            break;
        case 'aktiv':
            if (RequestHelper::verifyGPCDataInt('s2') > 1) {
                $smarty->assign('cBackPage', 'tab=aktiv&s2=' . RequestHelper::verifyGPCDataInt('s2'))
                       ->assign('cSeite', RequestHelper::verifyGPCDataInt('s2'));
            }
            break;
        case 'kategorien':
            if (RequestHelper::verifyGPCDataInt('s3') > 1) {
                $smarty->assign('cBackPage', 'tab=kategorien&s3=' . RequestHelper::verifyGPCDataInt('s3'))
                       ->assign('cSeite', RequestHelper::verifyGPCDataInt('s3'));
            }
            break;
    }
}
$Sprachen     = Sprache::getAllLanguages();
$oSpracheNews = Shop::Lang()->getIsoFromLangID($_SESSION['kSprache']);
if (!$oSpracheNews) {
    $oSpracheNews = $db->select('tsprache', 'kSprache', (int)$_SESSION['kSprache']);
}
// News
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0 && FormHelper::validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_NEWS, $_POST, [CACHING_GROUP_OPTION, CACHING_GROUP_NEWS]);
    if (count($Sprachen) > 0) {
        // tnewsmonatspraefix loeschen
        $db->query('TRUNCATE tnewsmonatspraefix', \DB\ReturnType::AFFECTED_ROWS);

        foreach ($Sprachen as $oSpracheTMP) {
            $oNewsMonatsPraefix           = new stdClass();
            $oNewsMonatsPraefix->kSprache = $oSpracheTMP->kSprache;
            if (strlen($_POST['praefix_' . $oSpracheTMP->cISO]) > 0) {
                $oNewsMonatsPraefix->cPraefix = htmlspecialchars(
                    $_POST['praefix_' . $oSpracheTMP->cISO],
                    ENT_COMPAT | ENT_HTML401, JTL_CHARSET
                );
            } else {
                $oNewsMonatsPraefix->cPraefix = $oSpracheTMP->cISO === 'ger'
                    ? 'Newsuebersicht'
                    : 'Newsoverview';
            }
            $db->insert('tnewsmonatspraefix', $oNewsMonatsPraefix);
        }
    }
}

if (RequestHelper::verifyGPCDataInt('news') === 1 && FormHelper::validateToken()) {
    // Neue News erstellen
    if ((isset($_POST['erstellen'], $_POST['news_erstellen']) && (int)$_POST['erstellen'] === 1)
        || (isset($_POST['news_erstellen']) && (int)$_POST['news_erstellen'] === 1)
    ) {
        $newsCategory_arr = $controller->getAllNewsCategories(false);
        // News erstellen, $newsCategory_arr leer = Fehler ausgeben
        if (count($newsCategory_arr) > 0) {
            $step = 'news_erstellen';
            $smarty->assign('oNewsKategorie_arr', $newsCategory_arr)
                   ->assign('oPossibleAuthors_arr',
                       $author->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
        } else {
            $cFehler .= 'Fehler: Bitte legen Sie zuerst eine Newskategorie an.<br />';
            $step    = 'news_uebersicht';
        }
    } elseif ((isset($_POST['erstellen'], $_POST['news_kategorie_erstellen'])
            && (int)$_POST['erstellen'] === 1)
        || (isset($_POST['news_kategorie_erstellen']) && (int)$_POST['news_kategorie_erstellen'] === 1)
    ) {
        $step = 'news_kategorie_erstellen';
    } elseif (RequestHelper::verifyGPCDataInt('nkedit') === 1) { // Newskommentar editieren
        if (RequestHelper::verifyGPCDataInt('kNews') > 0) {
            if (isset($_POST['newskommentarsavesubmit'])) {
                if (speicherNewsKommentar(RequestHelper::verifyGPCDataInt('kNewsKommentar'), $_POST)) {
                    $step     = 'news_vorschau';
                    $cHinweis .= 'Der Newskommentar wurde erfolgreich editiert.<br />';

                    if (RequestHelper::verifyGPCDataInt('nFZ') === 1) {
                        header('Location: freischalten.php');
                        exit();
                    }
                    $tab = RequestHelper::verifyGPDataString('tab');
                    if ($tab === 'aktiv') {
                        newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis, [
                            'news'  => '1',
                            'nd'    => '1',
                            'kNews' => RequestHelper::verifyGPCDataInt('kNews'),
                            'token' => $_SESSION['jtl_token'],
                        ]);
                    } else {
                        newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis);
                    }
                } else {
                    $step                           = 'news_kommentar_editieren';
                    $cFehler                        .= 'Fehler: Bitte überprüfen Sie Ihre Eingaben.<br />';
                    $oNewsKommentar                 = new stdClass();
                    $oNewsKommentar->kNewsKommentar = $_POST['kNewsKommentar'];
                    $oNewsKommentar->kNews          = $_POST['kNews'];
                    $oNewsKommentar->cName          = $_POST['cName'];
                    $oNewsKommentar->cKommentar     = $_POST['cKommentar'];
                    $smarty->assign('oNewsKommentar', $oNewsKommentar);
                }
            } else {
                $step = 'news_kommentar_editieren';
                $smarty->assign('oNewsKommentar', $db->select(
                    'tnewskommentar',
                    'kNewsKommentar',
                    RequestHelper::verifyGPCDataInt('kNewsKommentar'))
                );
                if (RequestHelper::verifyGPCDataInt('nFZ') === 1) {
                    $smarty->assign('nFZ', 1);
                }
            }
        }
    } elseif (isset($_POST['news_speichern']) && (int)$_POST['news_speichern'] === 1) { // News speichern
        $controller->createOrUpdateNewsItem($_POST, $languages, $author);
    } elseif (isset($_POST['news_loeschen']) && (int)$_POST['news_loeschen'] === 1) { // News loeschen
        if (isset($_POST['kNews']) && is_array($_POST['kNews']) && count($_POST['kNews']) > 0) {
            $controller->deleteNewsItems($_POST['kNews'], $author);

            $cHinweis .= 'Ihre markierten News wurden erfolgreich gelöscht.<br />';
            newsRedirect('aktiv', $cHinweis);
        } else {
            $cFehler .= 'Fehler: Sie müssen mindestens eine News ausgewählt haben.<br />';
        }
    } elseif (isset($_POST['news_kategorie_speichern']) && (int)$_POST['news_kategorie_speichern'] === 1) {
        //Newskategorie speichern
        $controller->createOrUpdateCategory($_POST, $languages);

    } elseif (isset($_POST['news_kategorie_loeschen']) && (int)$_POST['news_kategorie_loeschen'] === 1) {
        // Newskategorie loeschen
        $step = 'news_uebersicht';
        if (isset($_POST['kNewsKategorie']) && loescheNewsKategorie($_POST['kNewsKategorie'])) {
            $cHinweis .= 'Ihre markierten Newskategorien wurden erfolgreich gelöscht.<br />';
            newsRedirect('kategorien', $cHinweis);
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens eine Newskategorie.<br />';
        }
    } elseif (isset($_GET['newskategorie_editieren']) && (int)$_GET['newskategorie_editieren'] === 1) {
        // Newskategorie editieren
        // Soll Preview geloescht werden?
        $kNewsKategorie = (int)$_GET['kNewsKategorie'];
        if (strlen(RequestHelper::verifyGPDataString('delpic')) > 0) {
            if (loescheNewsBild(RequestHelper::verifyGPDataString('delpic'), $kNewsKategorie, $cUploadVerzeichnisKat)) {
                $cHinweis .= 'Ihr ausgewähltes Newsbild wurde erfolgreich gelöscht.';
            } else {
                $cFehler .= 'Fehler: Ihr ausgewähltes Newsbild konnte nicht gelöscht werden.';
            }
        }
        if (isset($_GET['kNewsKategorie']) && (int)$_GET['kNewsKategorie'] > 0) {
            $step         = 'news_kategorie_erstellen';
            $newsCategory = new \News\Category($db);//editiereNewskategorie($_GET['kNewsKategorie'], $_SESSION['kSprache']);
            $newsCategory->load((int)$_GET['kNewsKategorie'], false);
            if ($newsCategory->getID() > 0) {
                $smarty->assign('oNewsKategorie', $newsCategory);
                // Hole Bilder
                if (is_dir($cUploadVerzeichnisKat . $newsCategory->getID())) {
                    $smarty->assign('oDatei_arr',
                        holeNewsKategorieBilder($newsCategory->getID(), $cUploadVerzeichnisKat));
                }
            } else {
                $step    = 'news_uebersicht';
                $cFehler .= 'Fehler: Die Newskategorie mit der ID "' . (int)$_GET['kNewsKategorie'] .
                    '" konnte nicht gefunden werden.<br />';
            }
        }
    } elseif (isset($_POST['newskommentar_freischalten'])
        && (int)$_POST['newskommentar_freischalten']
        && !isset($_POST['kommentareloeschenSubmit'])
    ) { // Kommentare freischalten
        if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
            foreach ($_POST['kNewsKommentar'] as $kNewsKommentar) {
                $kNewsKommentar = (int)$kNewsKommentar;
                $upd            = new stdClass();
                $upd->nAktiv    = 1;
                $db->update('tnewskommentar', 'kNewsKommentar', $kNewsKommentar, $upd);
            }
            $cHinweis .= 'Ihre markierten Newskommentare wurden erfolgreich freigeschaltet.<br />';
            $tab      = RequestHelper::verifyGPDataString('tab');
            newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis);
        } else {
            $cFehler .= 'Fehler: Bitte markieren Sie mindestens einen Newskommentar.<br />';
        }
    } elseif (isset($_POST['newskommentar_freischalten'], $_POST['kommentareloeschenSubmit'])) {
        if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
            foreach ($_POST['kNewsKommentar'] as $kNewsKommentar) {
                $db->delete('tnewskommentar', 'kNewsKommentar', (int)$kNewsKommentar);
            }

            $cHinweis .= 'Ihre markierten Kommentare wurden erfolgreich gelöscht.<br />';
            $tab      = RequestHelper::verifyGPDataString('tab');
            newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis);
        } else {
            $cFehler .= 'Fehler: Sie müssen mindestens einen Kommentar markieren.<br />';
        }
    }
    if ((isset($_GET['news_editieren']) && (int)$_GET['news_editieren'] === 1) ||
        ($continueWith !== false && $continueWith > 0)) {
        $newsCategory_arr = News::getAllNewsCategories($_SESSION['kSprache'], true);
        $kNews            = ($continueWith !== false && $continueWith > 0)
            ? $continueWith
            : (int)$_GET['kNews'];
        // Sollen einzelne Newsbilder geloescht werden?
        if (strlen(RequestHelper::verifyGPDataString('delpic')) > 0) {
            if (loescheNewsBild(RequestHelper::verifyGPDataString('delpic'), $kNews, $cUploadVerzeichnis)) {
                $cHinweis .= 'Ihr ausgewähltes Newsbild wurde erfolgreich gelöscht.';
            } else {
                $cFehler .= 'Fehler: Ihr ausgewähltes Newsbild konnte nicht gelöscht werden.';
            }
        }

        if ($kNews > 0 && count($newsCategory_arr) > 0) {
            $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories(false))
                   ->assign('oAuthor', $author->getAuthor('NEWS', $kNews))
                   ->assign('oPossibleAuthors_arr',
                       $author->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
            $step  = 'news_editieren';
            $oNews = new \News\Item($db);
            $oNews->load($kNews);

            if ($oNews->getID() > 0) {
//                $oNews->kKundengruppe_arr = StringHandler::parseSSK($oNews->cKundengruppe);
                // Hole Bilder
                if (is_dir($cUploadVerzeichnis . $oNews->getID())) {
                    $smarty->assign('oDatei_arr', holeNewsBilder($oNews->getID(), $cUploadVerzeichnis));
                }
                // NewskategorieNews
                $newsCategoryNews_arr = \Functional\map($db->query(
                    'SELECT DISTINCT(kNewsKategorie)
                        FROM tnewskategorienews
                        WHERE kNews = ' . $oNews->getID(),
                    \DB\ReturnType::ARRAY_OF_OBJECTS
                ), function ($e) {
                    return $e->kNewsKategorie;
                });

                $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories())
                       ->assign('oNews', $oNews);
            }
        } else {
            $cFehler .= 'Fehler: Bitte legen Sie zuerst eine Newskategorie an.<br />';
            $step    = 'news_uebersicht';
        }
    }

    // News Vorschau
    if ($step === 'news_vorschau' || RequestHelper::verifyGPCDataInt('nd') === 1) {
        if (RequestHelper::verifyGPCDataInt('kNews')) {
            $step  = 'news_vorschau';
            $kNews = RequestHelper::verifyGPCDataInt('kNews');
            $oNews = new \News\Item($db);
            $oNews->load($kNews);

            if ($oNews->getID() > 0) {
                if (is_dir($cUploadVerzeichnis . $oNews->getID())) {
                    $smarty->assign('oDatei_arr', holeNewsBilder($oNews->getID(), $cUploadVerzeichnis));
                }
                $smarty->assign('oNews', $oNews);
                if ((isset($_POST['kommentare_loeschen']) && (int)$_POST['kommentare_loeschen'] === 1)
                    || isset($_POST['kommentareloeschenSubmit'])
                ) {
                    if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
                        foreach ($_POST['kNewsKommentar'] as $kNewsKommentar) {
                            $db->delete('tnewskommentar', 'kNewsKommentar', (int)$kNewsKommentar);
                        }

                        $cHinweis .= 'Ihre markierten Kommentare wurden erfolgreich gelöscht.<br />';
                        $tab      = RequestHelper::verifyGPDataString('tab');
                        newsRedirect(empty($tab) ? 'inaktiv' : $tab, $cHinweis, [
                            'news'  => '1',
                            'nd'    => '1',
                            'kNews' => $oNews->getID(),
                            'token' => $_SESSION['jtl_token'],
                        ]);
                    } else {
                        $cFehler .= 'Fehler: Sie müssen mindestens einen Kommentar markieren.<br />';
                    }
                }

                $smarty->assign('oNewsKommentar_arr', $oNews->getComments()->getItems());
            }
        }
    }
    Shop::Cache()->flushTags([CACHING_GROUP_NEWS]);
}
// Hole News aus DB
if ($step === 'news_uebersicht') {
    $oNews_arr = $controller->getAllNews();//getAllNews();

    // Newskommentare die auf eine Freischaltung warten
    $oNewsKommentar_arr = $controller->getNonActivatedComments($_SESSION['kSprache']);
    $oConfig_arr        = $db->selectAll(
        'teinstellungenconf',
        'kEinstellungenSektion',
        CONF_NEWS,
        '*',
        'nSort'
    );
    $configCount        = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        if ($oConfig_arr[$i]->cInputTyp === 'selectbox') {
            $oConfig_arr[$i]->ConfWerte = $db->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                (int)$oConfig_arr[$i]->kEinstellungenConf,
                '*',
                'nSort'
            );
        }
        $oSetValue                      = $db->select(
            'teinstellungen',
            'kEinstellungenSektion',
            CONF_NEWS,
            'cName',
            $oConfig_arr[$i]->cWertName
        );
        $oConfig_arr[$i]->gesetzterWert = $oSetValue->cWert ?? null;
    }

    // Praefix
    $oNewsMonatsPraefix_arr = [];
    foreach ($Sprachen as $i => $oSprache) {
        $oNewsMonatsPraefix_arr[$i]                = new stdClass();
        $oNewsMonatsPraefix_arr[$i]->kSprache      = $oSprache->kSprache;
        $oNewsMonatsPraefix_arr[$i]->cNameEnglisch = $oSprache->cNameEnglisch;
        $oNewsMonatsPraefix_arr[$i]->cNameDeutsch  = $oSprache->cNameDeutsch;
        $oNewsMonatsPraefix_arr[$i]->cISOSprache   = $oSprache->cISO;
        $oNewsMonatsPraefix                        = $db->select(
            'tnewsmonatspraefix',
            'kSprache',
            (int)$oSprache->kSprache
        );
        $oNewsMonatsPraefix_arr[$i]->cPraefix      = $oNewsMonatsPraefix->cPraefix ?? null;
    }
    $newsCategory_arr = $controller->getAllNewsCategories();
    $oPagiKommentar   = (new Pagination('kommentar'))
        ->setItemArray($oNewsKommentar_arr)
        ->assemble();
    $oPagiNews        = (new Pagination('news'))
        ->setItemArray($oNews_arr)
        ->assemble();
    $oPagiKats        = (new Pagination('kats'))
        ->setItemArray($newsCategory_arr)
        ->assemble();
    $smarty->assign('oConfig_arr', $oConfig_arr)
           ->assign('oNewsKommentar_arr', $oPagiKommentar->getPageItems())
           ->assign('oNews_arr', $oPagiNews->getPageItems())
           ->assign('oNewsKategorie_arr', $oPagiKats->getPageItems())
           ->assign('oNewsMonatsPraefix_arr', $oNewsMonatsPraefix_arr)
           ->assign('oPagiKommentar', $oPagiKommentar)
           ->assign('oPagiNews', $oPagiNews)
           ->assign('oPagiKats', $oPagiKats);
} elseif ($step === 'news_kategorie_erstellen') {
    $newsCategory = new \News\Category($db);
    foreach ($languages as $language) {
        $newsCategory->setName('', $language->kSprache);
    }
    $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories())
           ->assign('oNewsKategorie', $newsCategory);
}

if (!empty($_SESSION['news.cHinweis'])) {
    $cHinweis .= $_SESSION['news.cHinweis'];
    unset($_SESSION['news.cHinweis']);
}

$nMaxFileSize      = getMaxFileSize(ini_get('upload_max_filesize'));
$oKundengruppe_arr = \Functional\map($db->query(
    'SELECT kKundengruppe, cName
        FROM tkundengruppe
        ORDER BY cStandard DESC',
    \DB\ReturnType::ARRAY_OF_OBJECTS
), function ($e) {
    $e->kKundengruppe = (int)$e->kKundengruppe;

    return $e;
});

$smarty->assign('oKundengruppe_arr', $oKundengruppe_arr)
       ->assign('hinweis', $cHinweis)
       ->assign('sprachen', $languages)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('Sprachen', $Sprachen)
       ->assign('nMaxFileSize', $nMaxFileSize)
       ->assign('kSprache', (int)$_SESSION['kSprache'])
       ->display('news.tpl');
