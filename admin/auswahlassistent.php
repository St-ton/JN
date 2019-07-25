<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Extensions\SelectionWizard\Group;
use JTL\Extensions\SelectionWizard\Question;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Nice;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('EXTENSION_SELECTIONWIZARD_VIEW', true, true);
$step        = '';
$nice        = Nice::getInstance();
$tab         = 'uebersicht';
$alertHelper = Shop::Container()->getAlertService();

JTL\Shop::Container()->getGetText()->loadConfigLocales();

if ($nice->checkErweiterung(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
    $group    = new Group();
    $question = new Question();
    $step     = 'uebersicht';
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
            $question->cFrage                  = htmlspecialchars(
                $_POST['cFrage'],
                ENT_COMPAT | ENT_HTML401,
                JTL_CHARSET
            );
            $question->kMerkmal                = (int)$_POST['kMerkmal'];
            $question->kAuswahlAssistentGruppe = (int)$_POST['kAuswahlAssistentGruppe'];
            $question->nSort                   = (int)$_POST['nSort'];
            $question->nAktiv                  = (int)$_POST['nAktiv'];

            $checks = [];
            if (isset($_POST['kAuswahlAssistentFrage']) && (int)$_POST['kAuswahlAssistentFrage'] > 0) {
                $question->kAuswahlAssistentFrage = (int)$_POST['kAuswahlAssistentFrage'];
                $checks                           = $question->updateQuestion();
            } else {
                $checks = $question->saveQuestion();
            }

            if ((!is_array($checks) && $checks) || count($checks) === 0) {
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successQuestionSaved'), 'successQuestionSaved');
                $tab = 'uebersicht';
            } elseif (is_array($checks) && count($checks) > 0) {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
                $smarty->assign('cPost_arr', Text::filterXSS($_POST))
                    ->assign('cPlausi_arr', $checks)
                    ->assign('kAuswahlAssistentFrage', (int)($_POST['kAuswahlAssistentFrage'] ?? 0));
            }
        }
    } elseif (isset($_GET['a'], $_GET['q'])
        && $_GET['a'] === 'delQuest'
        && (int)$_GET['q'] > 0
        && Form::validateToken()
    ) {
        if ($question->deleteQuestion([$_GET['q']])) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successQuestionDeleted'), 'successQuestionDeleted');
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorQuestionDeleted'), 'errorQuestionDeleted');
        }
    } elseif (isset($_GET['a']) && $_GET['a'] === 'editQuest' && (int)$_GET['q'] > 0 && Form::validateToken()) {
        $step = 'edit-question';
        $smarty->assign('oFrage', new Question((int)$_GET['q'], false));
    }

    if (isset($_POST['a']) && Form::validateToken()) {
        if ($_POST['a'] === 'addGrp') {
            $group->kSprache      = (int)$_SESSION['kSprache'];
            $group->cName         = htmlspecialchars(
                $_POST['cName'],
                ENT_COMPAT | ENT_HTML401,
                JTL_CHARSET
            );
            $group->cBeschreibung = $_POST['cBeschreibung'];
            $group->nAktiv        = (int)$_POST['nAktiv'];

            $checks = [];
            if (isset($_POST['kAuswahlAssistentGruppe']) && (int)$_POST['kAuswahlAssistentGruppe'] > 0) {
                $group->kAuswahlAssistentGruppe = (int)$_POST['kAuswahlAssistentGruppe'];
                $checks                         = $group->updateGroup($_POST);
            } else {
                $checks = $group->saveGroup($_POST);
            }
            if ((!is_array($checks) && $checks) || count($checks) === 0) {
                $step = 'uebersicht';
                $tab  = 'uebersicht';
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successGroupSaved'), 'successGroupSaved');
            } elseif (is_array($checks) && count($checks) > 0) {
                $step = 'edit-group';
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
                $smarty->assign('cPost_arr', Text::filterXSS($_POST))
                    ->assign('cPlausi_arr', $checks)
                    ->assign('kAuswahlAssistentGruppe', (isset($_POST['kAuswahlAssistentGruppe'])
                        ? (int)$_POST['kAuswahlAssistentGruppe']
                        : 0));
            }
        } elseif ($_POST['a'] === 'delGrp') {
            if ($group->deleteGroup($_POST['kAuswahlAssistentGruppe_arr'] ?? [])) {
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
        $smarty->assign('oGruppe', new Group((int)$_GET['g'], false, false, true));
    }
    if ($step === 'uebersicht') {
        $smarty->assign(
            'oAuswahlAssistentGruppe_arr',
            $group->getGroups($_SESSION['kSprache'], false, false, true)
        );
    } elseif ($step === 'edit-group') {
        $smarty->assign('oLink_arr', Wizard::getLinks());
    } elseif ($step === 'edit-question') {
        $defaultLanguage = Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y');
        $select          = 'tmerkmal.*';
        $join            = '';
        if ((int)$defaultLanguage->kSprache !== (int)$_SESSION['kSprache']) {
            $select = 'tmerkmalsprache.*';
            $join   = ' JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                            AND tmerkmalsprache.kSprache = ' . (int)$_SESSION['kSprache'];
        }
        $attributes = Shop::Container()->getDB()->query(
            'SELECT ' . $select . '
                FROM tmerkmal
                ' . $join . '
                ORDER BY tmerkmal.nSort',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $smarty->assign('oMerkmal_arr', $attributes)
            ->assign(
                'oAuswahlAssistentGruppe_arr',
                $group->getGroups($_SESSION['kSprache'], false, false, true)
            );
    }
} else {
    $smarty->assign('noModule', true);
}
$smarty->assign('step', $step)
    ->assign('cTab', $tab)
    ->assign('oConfig_arr', getAdminSectionSettings(CONF_AUSWAHLASSISTENT))
    ->display('auswahlassistent.tpl');
