<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\CheckBox;
use JTL\Shop;
use JTL\Sprache;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\DB\ReturnType;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CHECKBOXES_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'checkbox_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$alertHelper  = Shop::Container()->getAlertService();
$step         = 'uebersicht';
$itemsPerPage = 15;
$languages    = Sprache::getAllLanguages();
$checkbox     = new CheckBox();
$tab          = $step;
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $tab = Request::verifyGPDataString('tab');
}
if (isset($_POST['erstellenShowButton'])) {
    $tab = 'erstellen';
} elseif (Request::verifyGPCDataInt('uebersicht') === 1 && Form::validateToken()) {
    $checkboxIDs = $_POST['kCheckBox'];
    if (isset($_POST['checkboxAktivierenSubmit'])) {
        $checkbox->aktivateCheckBox($checkboxIDs);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCheckboxActivate'), 'successCheckboxActivate');
    } elseif (isset($_POST['checkboxDeaktivierenSubmit'])) {
        $checkbox->deaktivateCheckBox($checkboxIDs);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCheckboxDeactivate'), 'successCheckboxDeactivate');
    } elseif (isset($_POST['checkboxLoeschenSubmit'])) {
        $checkbox->deleteCheckBox($checkboxIDs);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCheckboxDelete'), 'successCheckboxDelete');
    }
} elseif (Request::verifyGPCDataInt('edit') > 0) {
    $checkboxID = Request::verifyGPCDataInt('edit');
    $step       = 'erstellen';
    $tab        = $step;
    $smarty->assign('oCheckBox', new CheckBox($checkboxID));
} elseif (Request::verifyGPCDataInt('erstellen') === 1 && Form::validateToken()) {
    $step       = 'erstellen';
    $checkboxID = Request::verifyGPCDataInt('kCheckBox');
    $checks     = plausiCheckBox($_POST, $languages);
    if (count($checks) === 0) {
        $checkbox = speicherCheckBox($_POST, $languages);
        $step     = 'uebersicht';
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successCheckboxCreate'), 'successCheckboxCreate');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
        $smarty->assign('cPost_arr', Text::filterXSS($_POST))
               ->assign('cPlausi_arr', $checks);
        if ($checkboxID > 0) {
            $smarty->assign('kCheckBox', $checkboxID);
        }
    }
    $tab = $step;
}

$pagination = (new Pagination())
    ->setItemCount($checkbox->getAllCheckBoxCount())
    ->assemble();
$smarty->assign('oCheckBox_arr', $checkbox->getAllCheckBox('LIMIT ' . $pagination->getLimitSQL()))
       ->assign('oPagination', $pagination)
       ->assign('cAnzeigeOrt_arr', CheckBox::gibCheckBoxAnzeigeOrte())
       ->assign('CHECKBOX_ORT_REGISTRIERUNG', CHECKBOX_ORT_REGISTRIERUNG)
       ->assign('CHECKBOX_ORT_BESTELLABSCHLUSS', CHECKBOX_ORT_BESTELLABSCHLUSS)
       ->assign('CHECKBOX_ORT_NEWSLETTERANMELDUNG', CHECKBOX_ORT_NEWSLETTERANMELDUNG)
       ->assign('CHECKBOX_ORT_KUNDENDATENEDITIEREN', CHECKBOX_ORT_KUNDENDATENEDITIEREN)
       ->assign('CHECKBOX_ORT_KONTAKT', CHECKBOX_ORT_KONTAKT)
       ->assign('oSprache_arr', $languages)
       ->assign('oKundengruppe_arr', Shop::Container()->getDB()->query(
           'SELECT * 
                FROM tkundengruppe 
                ORDER BY cName',
           ReturnType::ARRAY_OF_OBJECTS
       ))
       ->assign('oLink_arr', Shop::Container()->getDB()->query(
           'SELECT * 
              FROM tlink 
              ORDER BY cName',
           ReturnType::ARRAY_OF_OBJECTS
       ))
       ->assign('oCheckBoxFunktion_arr', $checkbox->getCheckBoxFunctions())
       ->assign('step', $step)
       ->assign('cTab', $tab)
       ->display('checkbox.tpl');
