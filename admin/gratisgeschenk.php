<?php

use JTL\Alert\Alert;
use JTL\Helpers\Request;
use JTL\Pagination\Pagination;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_GIFT_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'gratisgeschenk_inc.php';

$settingsIDs = [1143, 1144, 1145, 1146];
if (mb_strlen(Request::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', Request::verifyGPDataString('tab'));
}
if (Request::verifyGPCDataInt('einstellungen') === 1) {
    Shop::Container()->getAlertService()->addAlert(
        Alert::TYPE_SUCCESS,
        saveAdminSettings($settingsIDs, $_POST),
        'saveSettings'
    );
}
$oPagiAktiv     = (new Pagination('aktiv'))
    ->setItemCount(gibAnzahlAktiverGeschenke())
    ->assemble();
$oPagiHaeufig   = (new Pagination('haeufig'))
    ->setItemCount(gibAnzahlHaeufigGekaufteGeschenke())
    ->assemble();
$oPagiLetzte100 = (new Pagination('letzte100'))
    ->setItemCount(gibAnzahlLetzten100Geschenke())
    ->assemble();

$smarty->assign('oPagiAktiv', $oPagiAktiv)
       ->assign('oPagiHaeufig', $oPagiHaeufig)
       ->assign('oPagiLetzte100', $oPagiLetzte100)
       ->assign('oAktiveGeschenk_arr', holeAktiveGeschenke(' LIMIT ' . $oPagiAktiv->getLimitSQL()))
       ->assign('oHaeufigGeschenk_arr', holeHaeufigeGeschenke(' LIMIT ' . $oPagiHaeufig->getLimitSQL()))
       ->assign('oLetzten100Geschenk_arr', holeLetzten100Geschenke(' LIMIT ' . $oPagiLetzte100->getLimitSQL()))
       ->assign('oConfig_arr', getAdminSectionSettings($settingsIDs))
       ->assign('ART_ATTRIBUT_GRATISGESCHENKAB', ART_ATTRIBUT_GRATISGESCHENKAB)
       ->display('gratisgeschenk.tpl');
