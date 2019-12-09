<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\Helpers\Request;
use JTL\Link\Link;
use JTL\Nice;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Survey\Controller;
use JTL\Survey\Survey;
use JTL\Survey\SurveyQuestionFactory;

require_once __DIR__ . '/includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES_EXT . 'umfrage_inc.php';

Shop::run();
Shop::setPageType(PAGE_UMFRAGE);
$smarty         = Shop::Smarty();
$params         = Shop::getParameters();
$alertHelper    = Shop::Container()->getAlertService();
$cCanonicalURL  = '';
$step           = 'umfrage_uebersicht';
$nAktuelleSeite = max(1, Request::verifyGPCDataInt('s'));
$sourveys       = [];
$linkHelper     = Shop::Container()->getLinkService();
$kLink          = $linkHelper->getSpecialPageLinkKey(LINKTYP_UMFRAGE);
$link           = (new Link(Shop::Container()->getDB()))->load($kLink);
$db             = Shop::Container()->getDB();
$controller     = new Controller($db, $smarty);
$surveyID       = $params['kUmfrage'];
if ($surveyID > 0) {
    $customerID = Frontend::getCustomer()->getID();
    $step       = 'umfrage_uebersicht';
    if ($customerID === 0 && Shop::getConfigValue(CONF_UMFRAGE, 'umfrage_einloggen') === 'Y') {
        header('Location: ' . $linkHelper->getStaticRoute('jtl.php') .
            '?u=' . $surveyID . '&r=' . R_LOGIN_UMFRAGE);
        exit();
    }
    $survey = new Survey($db, Nice::getInstance(), new SurveyQuestionFactory($db));
    $survey->load($surveyID);
    $controller->setSurvey($survey);
    if ($survey->getID() > 0 && $controller->checkAlreadyVoted($customerID, $_SESSION['oBesucher']->cID ?? null)) {
        $breadCrumbName = $survey->getName();
        $breadCrumbURL  = Shop::getURL() . '/'. $survey->getURL();
        $step           = 'umfrage_durchfuehren';
        if (isset($_POST['end'])) {
            $controller->saveAnswers($_POST);
            if ($controller->checkInputData($_POST) > 0) {
                $controller->setErrorMsg(Shop::Lang()->get('pollRequired', 'errorMessages'));
            } elseif ($_SESSION['Umfrage']->nEnde === 0) {
                $step = 'umfrage_ergebnis';
                executeHook(HOOK_UMFRAGE_PAGE_UMFRAGEERGEBNIS);
                $alertHelper->addAlert(Alert::TYPE_NOTE, $controller->bearbeiteUmfrageAuswertung(), 'pollNote');
            } else {
                $step = 'umfrage_uebersicht';
            }
        }
        if ($step === 'umfrage_durchfuehren') {
            $nAktuelleSeite = $controller->init(
                $survey,
                $nAktuelleSeite
            );
        }
        $_SESSION['Umfrage']->kUmfrage = $survey->getID();
        executeHook(HOOK_UMFRAGE_PAGE_DURCHFUEHRUNG);
    } else {
        $controller->setErrorMsg(Shop::Lang()->get('pollAlreadydid', 'errorMessages'));
    }
}
if ($step === 'umfrage_uebersicht') {
    $sourveys = $controller->getOverview();
    if (count($sourveys) === 0) {
        $controller->setErrorMsg(Shop::Lang()->get('pollNopoll', 'errorMessages'));
    }
    $cCanonicalURL = $linkHelper->getStaticRoute('umfrage.php');
    executeHook(HOOK_UMFRAGE_PAGE_UEBERSICHT);
}

if ($controller->getErrorMsg() !== '') {
    $alertHelper->addAlert(Alert::TYPE_ERROR, $controller->getErrorMsg(), 'pollError');
}

$smarty->assign('Link', $link)
       ->assign('step', $step)
       ->assign('oUmfrage_arr', $sourveys)
       ->assign('nAktuelleSeite', $nAktuelleSeite);

require PFAD_ROOT . PFAD_INCLUDES . 'letzterInclude.php';

executeHook(HOOK_UMFRAGE_PAGE);

$smarty->display('poll/index.tpl');

require PFAD_ROOT . PFAD_INCLUDES . 'profiler_inc.php';
