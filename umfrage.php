<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'umfrage_inc.php';

Shop::run();
Shop::setPageType(PAGE_UMFRAGE);
$smarty                 = Shop::Smarty();
$cParameter_arr         = Shop::getParameters();
$cHinweis               = '';
$cFehler                = '';
$cCanonicalURL          = '';
$step                   = 'umfrage_uebersicht';
$nAktuelleSeite         = 1;
$oUmfrageFrageTMP_arr   = [];
$oUmfrage_arr = [];
$Einstellungen          = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_UMFRAGE]);
$linkHelper             = Shop::Container()->getLinkService();
$kLink                  = $linkHelper->getSpecialPageLinkKey(LINKTYP_UMFRAGE);
$link                   = (new \Link\Link(Shop::Container()->getDB()))->load($kLink);
$AufgeklappteKategorien = new KategorieListe();
$startKat               = new Kategorie();
$startKat->kKategorie   = 0;
$AktuelleKategorie      = new Kategorie(RequestHelper::verifyGPCDataInt('kategorie'));
$AufgeklappteKategorien->getOpenCategories($AktuelleKategorie);

$db = Shop::Container()->getDB();
$controller = new \Survey\Controller($db, $smarty);
//Shop::dbg($_POST);
//unset($_SESSION['Umfrage']);
// Umfrage durchführen
if (isset($cParameter_arr['kUmfrage']) && $cParameter_arr['kUmfrage'] > 0) {
    $step = 'umfrage_uebersicht';
    // Umfrage durchführen
    if (($Einstellungen['umfrage']['umfrage_einloggen'] === 'Y'
            && isset($_SESSION['Kunde']->kKunde)
            && $_SESSION['Kunde']->kKunde > 0
        )
        || $Einstellungen['umfrage']['umfrage_einloggen'] === 'N'
    ) {

        $survey = new \Survey\Survey($db, Nice::getInstance(), new \Survey\SurveyQuestionFactory($db));
        $survey->load($cParameter_arr['kUmfrage']);

        $controller->setSurvey($survey);

        if ($survey->getID() > 0) {
            if ($controller->checkAlreadyVoted(Session\Session::Customer()->getID(), $_SESSION['oBesucher']->cID)) {
                $step = 'umfrage_durchfuehren';
                if (isset($_POST['end'])) {
                    $controller->saveAnswers($_POST);
                    if (pruefeEingabe($_POST) > 0) {
                        $cFehler .= Shop::Lang()->get('pollRequired', 'errorMessages') . '<br>';
                    } elseif ($_SESSION['Umfrage']->nEnde === 0) {
                        $step = 'umfrage_ergebnis';
                        executeHook(HOOK_UMFRAGE_PAGE_UMFRAGEERGEBNIS);
                        // Auswertung
                        bearbeiteUmfrageAuswertung($survey);
                    } else {
                        $step = 'umfrage_uebersicht';
                    }
                }
                if ($step === 'umfrage_durchfuehren') {
                    $oNavi_arr = [];
                    // Durchfuehrung
                    $oUmfrageFrageTMP_arr = $controller->bearbeiteUmfrageDurchfuehrung(
                        $survey,
                        $oNavi_arr,
                        $cParameter_arr['kSeite']
                    );
                }
                $_SESSION['Umfrage']->kUmfrage = $survey->getID();
                $smarty->assign('oUmfrage', $survey)
                       ->assign('oNavi_arr', baueSeitenNavi($oUmfrageFrageTMP_arr, $survey->getQuestionCount()))
                       ->assign('nAktuelleSeite', $cParameter_arr['kSeite'])
                       ->assign('nAnzahlSeiten', bestimmeAnzahlSeiten($oUmfrageFrageTMP_arr));

                executeHook(HOOK_UMFRAGE_PAGE_DURCHFUEHRUNG);
            } else {
                $cFehler .= Shop::Lang()->get('pollAlreadydid', 'errorMessages') . '<br />';
            }
        }
    } else {
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php') .
            '?u=' . $cParameter_arr['kUmfrage'] . '&r=' . R_LOGIN_UMFRAGE);
        exit();
    }
}

if ($step === 'umfrage_uebersicht') {
    $oUmfrage_arr = $controller->getOverview();
    if (count($oUmfrage_arr) === 0) {
        $cFehler .= Shop::Lang()->get('pollNopoll', 'errorMessages') . '<br />';
    }
    $cCanonicalURL = Shop::getURL() . '/umfrage.php';


    executeHook(HOOK_UMFRAGE_PAGE_UEBERSICHT);
}

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->assign('oUmfrage_arr', $oUmfrage_arr);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_UMFRAGE_PAGE);

$smarty->display('poll/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
