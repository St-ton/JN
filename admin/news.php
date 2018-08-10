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

$uploadDir      = PFAD_ROOT . PFAD_NEWSBILDER;
$uploadDirCat   = PFAD_ROOT . PFAD_NEWSKATEGORIEBILDER;
$newsCategories = [];
$db             = Shop::Container()->getDB();
$author         = ContentAuthor::getInstance();
$controller     = new \News\Admin\Controller($db, $smarty, Shop::Container()->getCache());
$newsCategory   = new \News\Category($db);
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
if (RequestHelper::verifyGPCDataInt('news') === 1 && FormHelper::validateToken()) {
    if (isset($_POST['einstellungen']) && (int)$_POST['einstellungen'] > 0) {
        $controller->setMsg(saveAdminSectionSettings(CONF_NEWS, $_POST, [CACHING_GROUP_OPTION, CACHING_GROUP_NEWS]));
        if (count($languages) > 0) {
            $db->query('TRUNCATE tnewsmonatspraefix', \DB\ReturnType::AFFECTED_ROWS);
            foreach ($languages as $lang) {
                $monthPrefix           = new stdClass();
                $monthPrefix->kSprache = $lang->kSprache;
                if (strlen($_POST['praefix_' . $lang->cISO]) > 0) {
                    $monthPrefix->cPraefix = htmlspecialchars(
                        $_POST['praefix_' . $lang->cISO],
                        ENT_COMPAT | ENT_HTML401, JTL_CHARSET
                    );
                } else {
                    $monthPrefix->cPraefix = $lang->cISO === 'ger'
                        ? 'Newsuebersicht'
                        : 'Newsoverview';
                }
                $db->insert('tnewsmonatspraefix', $monthPrefix);
            }
        }
    } elseif ((isset($_POST['erstellen'], $_POST['news_erstellen']) && (int)$_POST['erstellen'] === 1)
        || (isset($_POST['news_erstellen']) && (int)$_POST['news_erstellen'] === 1)
    ) {
        $newsCategories = $controller->getAllNewsCategories(false);
        if (count($newsCategories) > 0) {
            $newsItem = new \News\Item($db);
            $controller->setStep('news_erstellen');
            $smarty->assign('oNewsKategorie_arr', $newsCategories)
                   ->assign('oNews', $newsItem)
                   ->assign('oPossibleAuthors_arr', $author->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
        } else {
            $controller->setErrorMsg('Fehler: Bitte legen Sie zuerst eine Newskategorie an.');
            $controller->setStep('news_uebersicht');
        }
    } elseif ((isset($_POST['erstellen'], $_POST['news_kategorie_erstellen']) && (int)$_POST['erstellen'] === 1)
        || (isset($_POST['news_kategorie_erstellen']) && (int)$_POST['news_kategorie_erstellen'] === 1)
    ) {
        $controller->setStep('news_kategorie_erstellen');
    } elseif (RequestHelper::verifyGPCDataInt('nkedit') === 1 && RequestHelper::verifyGPCDataInt('kNews') > 0) {
        if (isset($_POST['newskommentarsavesubmit'])) {
            if ($controller->saveComment(RequestHelper::verifyGPCDataInt('kNewsKommentar'), $_POST)) {
                $controller->setStep('news_vorschau');
                $controller->setMsg('Der Newskommentar wurde erfolgreich editiert.');

                if (RequestHelper::verifyGPCDataInt('nFZ') === 1) {
                    header('Location: freischalten.php');
                    exit();
                }
                $tab = RequestHelper::verifyGPDataString('tab');
                if ($tab === 'aktiv') {
                    $controller->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $controller->getMsg(), [
                        'news'  => '1',
                        'nd'    => '1',
                        'kNews' => RequestHelper::verifyGPCDataInt('kNews'),
                        'token' => $_SESSION['jtl_token'],
                    ]);
                } else {
                    $controller->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $controller->getMsg());
                }
            } else {
                $controller->setStep('news_kommentar_editieren');
                $controller->setErrorMsg('Fehler: Bitte überprüfen Sie Ihre Eingaben.');
                $comment                 = new stdClass();
                $comment->kNewsKommentar = $_POST['kNewsKommentar'];
                $comment->kNews          = $_POST['kNews'];
                $comment->cName          = $_POST['cName'];
                $comment->cKommentar     = $_POST['cKommentar'];
                $smarty->assign('oNewsKommentar', $comment);
            }
        } else {
            $controller->setStep('news_kommentar_editieren');
            $comment = new \News\Comment($db);
            $comment->load(RequestHelper::verifyGPCDataInt('kNewsKommentar'));
            $smarty->assign('oNewsKommentar', $comment);
            if (RequestHelper::verifyGPCDataInt('nFZ') === 1) {
                $smarty->assign('nFZ', 1);
            }
        }
    } elseif (isset($_POST['news_speichern']) && (int)$_POST['news_speichern'] === 1) {
        $controller->createOrUpdateNewsItem($_POST, $languages, $author);
    } elseif (isset($_POST['news_loeschen']) && (int)$_POST['news_loeschen'] === 1) {
        if (isset($_POST['kNews']) && is_array($_POST['kNews']) && count($_POST['kNews']) > 0) {
            $controller->deleteNewsItems($_POST['kNews'], $author);
            $controller->setMsg('Ihre markierten News wurden erfolgreich gelöscht.');
            $controller->newsRedirect('aktiv', $controller->getMsg());
        } else {
            $controller->setErrorMsg('Fehler: Sie müssen mindestens eine News ausgewählt haben.');
        }
    } elseif (isset($_POST['news_kategorie_speichern']) && (int)$_POST['news_kategorie_speichern'] === 1) {
        $newsCategory = $controller->createOrUpdateCategory($_POST, $languages);
    } elseif (isset($_POST['news_kategorie_loeschen']) && (int)$_POST['news_kategorie_loeschen'] === 1) {
        $controller->setStep('news_uebersicht');
        if (isset($_POST['kNewsKategorie'])) {
            $controller->deleteCategories($_POST['kNewsKategorie']);
            $controller->setMsg('Ihre markierten Newskategorien wurden erfolgreich gelöscht.');
            $controller->newsRedirect('kategorien', $controller->getMsg());
        } else {
            $controller->setErrorMsg('Fehler: Bitte markieren Sie mindestens eine Newskategorie.');
        }
    } elseif (isset($_GET['newskategorie_editieren']) && (int)$_GET['newskategorie_editieren'] === 1) {
        if (strlen(RequestHelper::verifyGPDataString('delpic')) > 0) {
            if ($controller->deleteNewsImage(
                RequestHelper::verifyGPDataString('delpic'),
                (int)$_GET['kNewsKategorie'],
                $uploadDirCat)
            ) {
                $controller->setMsg('Ihr ausgewähltes Newsbild wurde erfolgreich gelöscht.');
            } else {
                $controller->setErrorMsg('Fehler: Ihr ausgewähltes Newsbild konnte nicht gelöscht werden.');
            }
        }
        if (isset($_GET['kNewsKategorie']) && (int)$_GET['kNewsKategorie'] > 0) {
            $controller->setStep('news_kategorie_erstellen');
            $newsCategory->load((int)$_GET['kNewsKategorie'], false);
            if ($newsCategory->getID() > 0) {
                $smarty->assign('oNewsKategorie', $newsCategory);
                if (is_dir($uploadDirCat . $newsCategory->getID())) {
                    $smarty->assign('oDatei_arr',
                        $controller->getCategoryImages($newsCategory->getID(), $uploadDirCat));
                }
            } else {
                $controller->setStep('news_uebersicht');
                $controller->setErrorMsg('Fehler: Die Newskategorie mit der ID "' . (int)$_GET['kNewsKategorie'] .
                    '" konnte nicht gefunden werden.');
            }
        }
    } elseif (isset($_POST['newskommentar_freischalten'])
        && (int)$_POST['newskommentar_freischalten']
        && !isset($_POST['kommentareloeschenSubmit'])
    ) {
        if (is_array($_POST['kNewsKommentar']) && count($_POST['kNewsKommentar']) > 0) {
            foreach ($_POST['kNewsKommentar'] as $id) {
                $db->update('tnewskommentar', 'kNewsKommentar', (int)$id, (object)['nAktiv' => 1]);
            }
            $controller->setMsg('Ihre markierten Newskommentare wurden erfolgreich freigeschaltet.');
            $tab = RequestHelper::verifyGPDataString('tab');
            $controller->newsRedirect(empty($tab) ? 'inaktiv' : $tab, $controller->getMsg());
        } else {
            $controller->setErrorMsg('Fehler: Bitte markieren Sie mindestens einen Newskommentar.');
        }
    } elseif (isset($_POST['newskommentar_freischalten'], $_POST['kNewsKommentar'], $_POST['kommentareloeschenSubmit'])) {
        $controller->deleteComments($_POST['kNewsKommentar']);
    }
    if ((isset($_GET['news_editieren']) && (int)$_GET['news_editieren'] === 1) || $controller->getContinueWith() > 0) {
        $newsCategories = News::getAllNewsCategories($_SESSION['kSprache'], true);
        $newsItemID     = $controller->getContinueWith() > 0
            ? $controller->getContinueWith()
            : (int)$_GET['kNews'];
        if (strlen(RequestHelper::verifyGPDataString('delpic')) > 0) {
            if ($controller->deleteNewsImage(RequestHelper::verifyGPDataString('delpic'), $newsItemID, $uploadDir)) {
                $controller->setMsg('Ihr ausgewähltes Newsbild wurde erfolgreich gelöscht.');
            } else {
                $controller->setErrorMsg('Fehler: Ihr ausgewähltes Newsbild konnte nicht gelöscht werden.');
            }
        }

        if ($newsItemID > 0 && count($newsCategories) > 0) {
            $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories(false))
                   ->assign('oAuthor', $author->getAuthor('NEWS', $newsItemID))
                   ->assign('oPossibleAuthors_arr', $author->getPossibleAuthors(['CONTENT_NEWS_SYSTEM_VIEW']));
            $controller->setStep('news_editieren');
            $newsItem = new \News\Item($db);
            $newsItem->load($newsItemID);

            if ($newsItem->getID() > 0) {
                if (is_dir($uploadDir . $newsItem->getID())) {
                    $smarty->assign('oDatei_arr', $controller->getNewsImages($newsItem->getID(), $uploadDir));
                }
                $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories())
                       ->assign('oNews', $newsItem);
            }
        } else {
            $controller->setErrorMsg('Fehler: Bitte legen Sie zuerst eine Newskategorie an.');
            $controller->setStep('news_uebersicht');
        }
    } elseif ($controller->getStep() === 'news_vorschau' || RequestHelper::verifyGPCDataInt('nd') === 1) {
        $controller->setStep('news_vorschau');
        $newsItemID = RequestHelper::verifyGPCDataInt('kNews');
        $newsItem   = new \News\Item($db);
        $newsItem->load($newsItemID);

        if ($newsItem->getID() > 0) {
            if (is_dir($uploadDir . $newsItem->getID())) {
                $smarty->assign('oDatei_arr', $controller->getNewsImages($newsItem->getID(), $uploadDir));
            }
            $smarty->assign('oNews', $newsItem);
            if ((isset($_POST['kommentare_loeschen']) && (int)$_POST['kommentare_loeschen'] === 1)
                || isset($_POST['kommentareloeschenSubmit'])
            ) {
                $controller->deleteComments($_POST['kNewsKommentar'], $newsItem);
            }

            $smarty->assign('oNewsKommentar_arr', $newsItem->getComments()->getItems());
        }
    }
}
if ($controller->getStep() === 'news_uebersicht') {
    $newsItems   = $controller->getAllNews();
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
} elseif ($controller->getStep() === 'news_kategorie_erstellen') {
    $smarty->assign('oNewsKategorie_arr', $controller->getAllNewsCategories())
           ->assign('oNewsKategorie', $newsCategory);
}

if (!empty($_SESSION['news.cHinweis'])) {
    $controller->setMsg($controller->getMsg() . $_SESSION['news.cHinweis']);
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
       ->assign('hinweis', $controller->getMsg())
       ->assign('sprachen', $languages)
       ->assign('fehler', $controller->getErrorMsg())
       ->assign('step', $controller->getStep())
       ->assign('nMaxFileSize', $maxFileSize)
       ->assign('kSprache', (int)$_SESSION['kSprache'])
       ->display('news.tpl');
