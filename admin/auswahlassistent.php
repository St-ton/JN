<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Extensions\AuswahlAssistent;
use JTL\Extensions\AuswahlAssistentFrage;
use JTL\Extensions\AuswahlAssistentGruppe;
use JTL\Nice;
use JTL\Shop;
use JTL\Helpers\Text;
use JTL\DB\ReturnType;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('EXTENSION_SELECTIONWIZARD_VIEW', true, true);
$step        = '';
$nice        = Nice::getInstance();
$tab         = 'uebersicht';
$alertHelper = Shop::Container()->getAlertService();

JTL\Shop::Container()->getGetText()->loadConfigLocales();

if ($nice->checkErweiterung(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
    $step = 'uebersicht';
    setzeSprache();

    if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
        $tab = Request::verifyGPDataString('tab');
    }
    if (isset($_POST['a']) && Form::validateToken()) {
        if ($_POST['a'] === 'newGrp') {
            $step = 'edit-group';
        } elseif ($_POST['a'] === 'newQuest') {
            $step = 'edit-question';
        } elseif ($_POST['a'] === 'addQuest') {
            $question                          = new AuswahlAssistentFrage();
            $question->cFrage                  = htmlspecialchars(
                $_POST['cFrage'],
                ENT_COMPAT | ENT_HTML401,
                JTL_CHARSET
            );
            $question->kMerkmal                = (int)$_POST['kMerkmal'];
            $question->kAuswahlAssistentGruppe = (int)$_POST['kAuswahlAssistentGruppe'];
            $question->nSort                   = (int)$_POST['nSort'];
            $question->nAktiv                  = (int)$_POST['nAktiv'];

            $cPlausi_arr = [];
            if (isset($_POST['kAuswahlAssistentFrage']) && (int)$_POST['kAuswahlAssistentFrage'] > 0) {
                $question->kAuswahlAssistentFrage = (int)$_POST['kAuswahlAssistentFrage'];
                $cPlausi_arr                      = $question->updateQuestion();
            } else {
                $cPlausi_arr = $question->saveQuestion();
            }

            if ((!is_array($cPlausi_arr) && $cPlausi_arr) || count($cPlausi_arr) === 0) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successQuestionSaved'), 'successQuestionSaved');
                $tab = 'uebersicht';
            } elseif (is_array($cPlausi_arr) && count($cPlausi_arr) > 0) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
                $smarty->assign('cPost_arr', Text::filterXSS($_POST))
                       ->assign('cPlausi_arr', $cPlausi_arr)
                       ->assign('kAuswahlAssistentFrage', (int)($_POST['kAuswahlAssistentFrage'] ?? 0));
            }
        }
    } elseif (isset($_GET['a'], $_GET['q'])
        && $_GET['a'] === 'delQuest'
        && (int)$_GET['q'] > 0
        && Form::validateToken()
    ) {
        if (AuswahlAssistentFrage::deleteQuestion(['kAuswahlAssistentFrage_arr' => [$_GET['q']]])) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successQuestionDeleted'), 'successQuestionDeleted');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorQuestionDeleted'), 'errorQuestionDeleted');
        }
    } elseif (isset($_GET['a']) && $_GET['a'] === 'editQuest' && (int)$_GET['q'] > 0 && Form::validateToken()) {
        $step = 'edit-question';
        $smarty->assign('oFrage', new AuswahlAssistentFrage((int)$_GET['q'], false));
    }

    if (isset($_POST['a']) && Form::validateToken()) {
        if ($_POST['a'] === 'addGrp') {
            $group                = new AuswahlAssistentGruppe();
            $group->kSprache      = (int)$_SESSION['kSprache'];
            $group->cName         = htmlspecialchars(
                $_POST['cName'],
                ENT_COMPAT | ENT_HTML401,
                JTL_CHARSET
            );
            $group->cBeschreibung = $_POST['cBeschreibung'];
            $group->nAktiv        = (int)$_POST['nAktiv'];

            $cPlausi_arr = [];
            if (isset($_POST['kAuswahlAssistentGruppe']) && (int)$_POST['kAuswahlAssistentGruppe'] > 0) {
                $group->kAuswahlAssistentGruppe = (int)$_POST['kAuswahlAssistentGruppe'];
                $cPlausi_arr                    = $group->updateGroup($_POST);
            } else {
                $cPlausi_arr = $group->saveGroup($_POST);
            }
            if ((!is_array($cPlausi_arr) && $cPlausi_arr) || count($cPlausi_arr) === 0) {
                $step = 'uebersicht';
                $tab  = 'uebersicht';
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successGroupSaved'), 'successGroupSaved');
            } elseif (is_array($cPlausi_arr) && count($cPlausi_arr) > 0) {
                $step = 'edit-group';
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
                $smarty->assign('cPost_arr', Text::filterXSS($_POST))
                       ->assign('cPlausi_arr', $cPlausi_arr)
                       ->assign('kAuswahlAssistentGruppe', (isset($_POST['kAuswahlAssistentGruppe'])
                           ? (int)$_POST['kAuswahlAssistentGruppe']
                           : 0));
            }
        } elseif ($_POST['a'] === 'delGrp') {
            if (AuswahlAssistentGruppe::deleteGroup($_POST)) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successGroupDeleted'), 'successGroupDeleted');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorGroupDeleted'), 'errorGroupDeleted');
            }
        } elseif ($_POST['a'] === 'saveSettings') {
            $step = 'uebersicht';
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                saveAdminSectionSettings(CONF_AUSWAHLASSISTENT, $_POST),
                'saveSettings'
            );
        }
    } elseif (isset($_GET['a'], $_GET['g'])
        && $_GET['a'] === 'editGrp'
        && (int)$_GET['g'] > 0
        && Form::validateToken()
    ) {
        $step = 'edit-group';
        $smarty->assign('oGruppe', new AuswahlAssistentGruppe($_GET['g'], false, false, true));
    }
    if ($step === 'uebersicht') {
        $smarty->assign(
            'oAuswahlAssistentGruppe_arr',
            AuswahlAssistentGruppe::getGroups($_SESSION['kSprache'], false, false, true)
        );
    } elseif ($step === 'edit-group') {
        $smarty->assign('oLink_arr', AuswahlAssistent::getLinks());
    } elseif ($step === 'edit-question') {
        $StdSprache = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
        $cSQLSelect = 'tmerkmal.*';
        $cSQLJoin   = '';
        if ((int)$StdSprache->kSprache !== (int)$_SESSION['kSprache']) {
            $cSQLSelect = 'tmerkmalsprache.*';
            $cSQLJoin   = ' JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                            AND tmerkmalsprache.kSprache = ' . (int)$_SESSION['kSprache'];
        }
        $attributes = Shop::Container()->getDB()->query(
            'SELECT ' . $cSQLSelect . '
                FROM tmerkmal
                ' . $cSQLJoin . '
                ORDER BY tmerkmal.nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $smarty->assign('oMerkmal_arr', $attributes)
               ->assign(
                   'oAuswahlAssistentGruppe_arr',
                   AuswahlAssistentGruppe::getGroups($_SESSION['kSprache'], false, false, true)
               );
    }
} else {
    $smarty->assign('noModule', true);
}
$smarty->assign('step', $step)
       ->assign('cTab', $tab)
       ->assign('AUSWAHLASSISTENT_ORT_STARTSEITE', AUSWAHLASSISTENT_ORT_STARTSEITE)
       ->assign('AUSWAHLASSISTENT_ORT_KATEGORIE', AUSWAHLASSISTENT_ORT_KATEGORIE)
       ->assign('AUSWAHLASSISTENT_ORT_LINK', AUSWAHLASSISTENT_ORT_LINK)
       ->assign('oConfig_arr', getAdminSectionSettings(CONF_AUSWAHLASSISTENT))
       ->display('auswahlassistent.tpl');
