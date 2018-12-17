<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CHECKBOXES_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'checkbox_inc.php';
/** @global Smarty\JTLSmarty $smarty */
$Einstellungen   = Shop::getSettings([CONF_CHECKBOX]);
$cHinweis        = '';
$cFehler         = '';
$cStep           = 'uebersicht';
$nAnzahlProSeite = 15;
$oSprach_arr     = Sprache::getAllLanguages();
$oCheckBox       = new CheckBox();
$cTab            = $cStep;
if (strlen(Request::verifyGPDataString('tab')) > 0) {
    $cTab = Request::verifyGPDataString('tab');
}
if (isset($_POST['erstellenShowButton'])) {
    $cTab = 'erstellen';
} elseif (Request::verifyGPCDataInt('uebersicht') === 1 && Form::validateToken()) {
    $kCheckBox_arr = $_POST['kCheckBox'];
    if (isset($_POST['checkboxAktivierenSubmit'])) {
        $oCheckBox->aktivateCheckBox($kCheckBox_arr);
        $cHinweis = 'Ihre markierten Checkboxen wurden erfolgreich aktiviert.';
    } elseif (isset($_POST['checkboxDeaktivierenSubmit'])) {
        $oCheckBox->deaktivateCheckBox($kCheckBox_arr);
        $cHinweis = 'Ihre markierten Checkboxen wurden erfolgreich deaktiviert.';
    } elseif (isset($_POST['checkboxLoeschenSubmit'])) {
        $oCheckBox->deleteCheckBox($kCheckBox_arr);
        $cHinweis = 'Ihre markierten Checkboxen wurden erfolgreich gelöscht.';
    }
} elseif (Request::verifyGPCDataInt('edit') > 0) {
    $kCheckBox = Request::verifyGPCDataInt('edit');
    $cStep     = 'erstellen';
    $cTab      = $cStep;
    $smarty->assign('oCheckBox', new CheckBox($kCheckBox));
} elseif (Request::verifyGPCDataInt('erstellen') === 1 && Form::validateToken()) {
    $cStep       = 'erstellen';
    $kCheckBox   = Request::verifyGPCDataInt('kCheckBox');
    $cPlausi_arr = plausiCheckBox($_POST, $oSprach_arr);
    if (count($cPlausi_arr) === 0) {
        $oCheckBox = speicherCheckBox($_POST, $oSprach_arr);
        $cStep     = 'uebersicht';
        $cHinweis  = 'Ihre Checkbox wurde erfolgreich erstellt.';
    } else {
        $cFehler = 'Fehler: Bitte füllen Sie alle nötigen Angaben aus!';
        $smarty->assign('cPost_arr', StringHandler::filterXSS($_POST))
               ->assign('cPlausi_arr', $cPlausi_arr);
        if ($kCheckBox > 0) {
            $smarty->assign('kCheckBox', $kCheckBox);
        }
    }
    $cTab = $cStep;
}

$oPagination   = (new Pagination())
    ->setItemCount($oCheckBox->getAllCheckBoxCount())
    ->assemble();
$oCheckBox_arr = $oCheckBox->getAllCheckBox('LIMIT ' . $oPagination->getLimitSQL());

$smarty->assign('oCheckBox_arr', $oCheckBox_arr)
       ->assign('oPagination', $oPagination)
       ->assign('cAnzeigeOrt_arr', CheckBox::gibCheckBoxAnzeigeOrte())
       ->assign('CHECKBOX_ORT_REGISTRIERUNG', CHECKBOX_ORT_REGISTRIERUNG)
       ->assign('CHECKBOX_ORT_BESTELLABSCHLUSS', CHECKBOX_ORT_BESTELLABSCHLUSS)
       ->assign('CHECKBOX_ORT_NEWSLETTERANMELDUNG', CHECKBOX_ORT_NEWSLETTERANMELDUNG)
       ->assign('CHECKBOX_ORT_KUNDENDATENEDITIEREN', CHECKBOX_ORT_KUNDENDATENEDITIEREN)
       ->assign('CHECKBOX_ORT_KONTAKT', CHECKBOX_ORT_KONTAKT)
       ->assign('oSprache_arr', $oSprach_arr)
       ->assign('oKundengruppe_arr', Shop::Container()->getDB()->query(
           'SELECT * 
                FROM tkundengruppe 
                ORDER BY cName',
           \DB\ReturnType::ARRAY_OF_OBJECTS
       ))
       ->assign('oLink_arr', Shop::Container()->getDB()->query(
           'SELECT * 
              FROM tlink 
              ORDER BY cName',
           \DB\ReturnType::ARRAY_OF_OBJECTS
       ))
       ->assign('oCheckBoxFunktion_arr', $oCheckBox->getCheckBoxFunctions())
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('step', $cStep)
       ->assign('cTab', $cTab)
       ->display('checkbox.tpl');
