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

$cHinweis       = '';
$cFehler        = '';
$step           = 'news_uebersicht';
$uploadDir      = PFAD_ROOT . PFAD_NEWSBILDER;
$uploadDirCat   = PFAD_ROOT . PFAD_NEWSKATEGORIEBILDER;
$newsCategories = [];
$continueWith   = false;
$db             = Shop::Container()->getDB();
$author         = ContentAuthor::getInstance();
$controller     = new \News\Admin\Controller($db, $smarty);
$languages      = Sprache::getAllLanguages();
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
if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0 && FormHelper::validateToken()) {
    $cHinweis .= saveAdminSectionSettings(CONF_NEWS, $_POST, [CACHING_GROUP_OPTION, CACHING_GROUP_NEWS]);
    if (count($languages) > 0) {
        $db->query('TRUNCATE tnewsmonatspraefix', \DB\ReturnType::AFFECTED_ROWS);
        foreach ($languages as $oSpracheTMP) {
            $monthPrefix           = new stdClass();
            $monthPrefix->kSprache = $oSpracheTMP->kSprache;
            if (strlen($_POST['praefix_' . $oSpracheTMP->cISO]) > 0) {
                $monthPrefix->cPraefix = htmlspecialchars(
                    $_POST['praefix_' . $oSpracheTMP->cISO],
                    ENT_COMPAT | ENT_HTML401, JTL_CHARSET
                );
            } else {
                $monthPrefix->cPraefix = $oSpracheTMP->cISO === 'ger'
                    ? 'Newsuebersicht'
                    : 'Newsoverview';
            }
            $db->insert('tnewsmonatspraefix', $monthPrefix);
        }
    }
}

if (RequestHelper::verifyGPCDataInt('news') === 1 && FormHelper::validateToken()) {
    // Neue News erstellen
    if ((isset($_POST['erstellen'], $_POST['news_erstellen']) && (int)$_POST['erstellen'] === 1)
        || (isset($_POST['news_erstellen']) && (int)$_POST['news_erstellen'] === 1)
    ) {
        $newsCategories = $controller->getAllNewsCategories(false);
        if (count($newsCategories) > 0) {
            $step = 'news_erstellen';
            $smarty->assign('oNewsKategorie_arr', $newsCategories)
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
                    $step                    = 'news_kommentar_editieren';
                    $cFehler                 .= 'Fehler: Bitte überprüfen Sie Ihre Eingaben.<br />';
                    $comment                 = new stdClass();
                    $comment->kNewsKommentar = $_POST['kNewsKommentar'];
                    $comment->kNews          = $_POST['kNews'];
                    $comment->cName          = $_POST['cName'];
                    $comment->cKommentar     = $_POST['cKommentar'];
                    $smarty->assign('oNewsKommentar', $comment);
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
        $categoryID = (int)$_GET['kNewsKategorie'];
        if (strlen(RequestHelper::verifyGPDataString('delpic')) > 0) {
            if (loescheNewsBild(RequestHelper::verifyGPDataString('delpic'), $categoryID, $uploadDirCat)) {
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
                if (is_dir($uploadDirCat . $newsCategory->getID())) {
                    $smarty->assign('oDatei_arr', holeNewsKategorieBilder($newsCategory->getID(), $uploadDirCat));
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
        $newsCategories = News::getAllNewsCategories($_SESSION['kSprache'], true);
        $newsItemID     = ($continueWith !== false && $continueWith > 0)
            ? $continueWith
            : (int)$_GET['kNews'];
        if (strlen(RequestHelper::verifyGPDataString('delpic')) > 0) {
            if (loescheNewsBild(RequestHelper::verifyGPDataString('delpic'), $newsItemID, $uploadDir)) {
                $cHinweis .= 'Ihr ausgewähltes Newsbild wurde erfolgreich gelöscht.';
            } else {
                $cFehler .= 'Fehler: Ihr ausgewähltes Newsbild konnte nicht gelöscht werden.';
            }
        }

        if ($newsItemID > 0 && count($newsCategories) > 0) {
            $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories(false))
                   ->assign('oAuthor', $author->getAuthor('NEWS', $newsItemID))
                   ->assign('oPossibleAuthors_arr', $author->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
            $step     = 'news_editieren';
            $newsItem = new \News\Item($db);
            $newsItem->load($newsItemID);

            if ($newsItem->getID() > 0) {
                if (is_dir($uploadDir . $newsItem->getID())) {
                    $smarty->assign('oDatei_arr', holeNewsBilder($newsItem->getID(), $uploadDir));
                }
                $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories())
                       ->assign('oNews', $newsItem);
            }
        } else {
            $cFehler .= 'Fehler: Bitte legen Sie zuerst eine Newskategorie an.<br />';
            $step    = 'news_uebersicht';
        }
    }

    if ($step === 'news_vorschau' || RequestHelper::verifyGPCDataInt('nd') === 1) {
        if (RequestHelper::verifyGPCDataInt('kNews')) {
            $step       = 'news_vorschau';
            $newsItemID = RequestHelper::verifyGPCDataInt('kNews');
            $newsItem   = new \News\Item($db);
            $newsItem->load($newsItemID);

            if ($newsItem->getID() > 0) {
                if (is_dir($uploadDir . $newsItem->getID())) {
                    $smarty->assign('oDatei_arr', holeNewsBilder($newsItem->getID(), $uploadDir));
                }
                $smarty->assign('oNews', $newsItem);
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
                            'kNews' => $newsItem->getID(),
                            'token' => $_SESSION['jtl_token'],
                        ]);
                    } else {
                        $cFehler .= 'Fehler: Sie müssen mindestens einen Kommentar markieren.<br />';
                    }
                }

                $smarty->assign('oNewsKommentar_arr', $newsItem->getComments()->getItems());
            }
        }
    }
    Shop::Cache()->flushTags([CACHING_GROUP_NEWS]);
}
// Hole News aus DB
if ($step === 'news_uebersicht') {
    $newsItems = $controller->getAllNews();//getAllNews();

    // Newskommentare die auf eine Freischaltung warten
    $comments    = $controller->getNonActivatedComments($_SESSION['kSprache']);
    $config      = $db->selectAll(
        'teinstellungenconf',
        'kEinstellungenSektion',
        CONF_NEWS,
        '*',
        'nSort'
    );
    $configCount = count($config);
    for ($i = 0; $i < $configCount; $i++) {
        if ($config[$i]->cInputTyp === 'selectbox') {
            $config[$i]->ConfWerte = $db->selectAll(
                'teinstellungenconfwerte',
                'kEinstellungenConf',
                (int)$config[$i]->kEinstellungenConf,
                '*',
                'nSort'
            );
        }
        $oSetValue                 = $db->select(
            'teinstellungen',
            'kEinstellungenSektion',
            CONF_NEWS,
            'cName',
            $config[$i]->cWertName
        );
        $config[$i]->gesetzterWert = $oSetValue->cWert ?? null;
    }

    // Praefix
    $prefixes = [];
    foreach ($languages as $i => $lang) {
        $prefixes[$i]                = new stdClass();
        $prefixes[$i]->kSprache      = $lang->kSprache;
        $prefixes[$i]->cNameEnglisch = $lang->cNameEnglisch;
        $prefixes[$i]->cNameDeutsch  = $lang->cNameDeutsch;
        $prefixes[$i]->cISOSprache   = $lang->cISO;
        $monthPrefix                 = $db->select(
            'tnewsmonatspraefix',
            'kSprache',
            (int)$lang->kSprache
        );
        $prefixes[$i]->cPraefix      = $monthPrefix->cPraefix ?? null;
    }
    $newsCategories     = $controller->getAllNewsCategories();
    $commentPagination  = (new Pagination('kommentar'))
        ->setItemArray($comments)
        ->assemble();
    $itemPagination     = (new Pagination('news'))
        ->setItemArray($newsItems)
        ->assemble();
    $categoryPagination = (new Pagination('kats'))
        ->setItemArray($newsCategories)
        ->assemble();
    $smarty->assign('oConfig_arr', $config)
           ->assign('oNewsKommentar_arr', $commentPagination->getPageItems())
           ->assign('oNews_arr', $itemPagination->getPageItems())
           ->assign('oNewsKategorie_arr', $categoryPagination->getPageItems())
           ->assign('oNewsMonatsPraefix_arr', $prefixes)
           ->assign('oPagiKommentar', $commentPagination)
           ->assign('oPagiNews', $itemPagination)
           ->assign('oPagiKats', $categoryPagination);
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

$maxFileSize    = getMaxFileSize(ini_get('upload_max_filesize'));
$customerGroups = \Functional\map($db->query(
    'SELECT kKundengruppe, cName
        FROM tkundengruppe
        ORDER BY cStandard DESC',
    \DB\ReturnType::ARRAY_OF_OBJECTS
), function ($e) {
    $e->kKundengruppe = (int)$e->kKundengruppe;

    return $e;
});

$smarty->assign('oKundengruppe_arr', $customerGroups)
       ->assign('hinweis', $cHinweis)
       ->assign('sprachen', $languages)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('nMaxFileSize', $maxFileSize)
       ->assign('kSprache', (int)$_SESSION['kSprache'])
       ->display('news.tpl');
