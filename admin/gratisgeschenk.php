<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_GIFT_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'gratisgeschenk_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$cHinweis          = '';
$cfehler           = '';
$settingsIDs       = [1143, 1144, 1145, 1146];
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
if (RequestHelper::verifyGPCDataInt('einstellungen') === 1) {
    $cHinweis .= saveAdminSettings($settingsIDs, $_POST);
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

$oAktiveGeschenk_arr     = holeAktiveGeschenke(' LIMIT ' . $oPagiAktiv->getLimitSQL());
$oHaeufigGeschenk_arr    = holeHaeufigeGeschenke(' LIMIT ' . $oPagiHaeufig->getLimitSQL());
$oLetzten100Geschenk_arr = holeLetzten100Geschenke(' LIMIT ' . $oPagiLetzte100->getLimitSQL());
$smarty->assign('oPagiAktiv', $oPagiAktiv)
       ->assign('oPagiHaeufig', $oPagiHaeufig)
       ->assign('oPagiLetzte100', $oPagiLetzte100)
       ->assign('oAktiveGeschenk_arr', $oAktiveGeschenk_arr)
       ->assign('oHaeufigGeschenk_arr', $oHaeufigGeschenk_arr)
       ->assign('oLetzten100Geschenk_arr', $oLetzten100Geschenk_arr)
       ->assign('oConfig_arr', getAdminSectionSettings($settingsIDs))
       ->assign('ART_ATTRIBUT_GRATISGESCHENKAB', ART_ATTRIBUT_GRATISGESCHENKAB)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cfehler)
       ->display('gratisgeschenk.tpl');
