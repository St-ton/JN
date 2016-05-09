<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('MODULE_GIFT_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'gratisgeschenk_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';

$cHinweis          = '';
$cfehler           = '';
$settingsIDs       = array(1143, 1144, 1145, 1146);
$nAnzahlProSeite   = 15;
$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(3, $nAnzahlProSeite);
// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
// Einstellungen
if (verifyGPCDataInteger('einstellungen') === 1) {
    $cHinweis .= saveAdminSettings($settingsIDs, $_POST);
}
// Config holen
$oConfig_arr = Shop::DB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE kEinstellungenConf IN (" . implode(',', $settingsIDs) . ")
        ORDER BY nSort", 2
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    $oConfig_arr[$i]->ConfWerte = Shop::DB()->query(
        "SELECT *
            FROM teinstellungenconfwerte
            WHERE kEinstellungenConf = " . (int)$oConfig_arr[$i]->kEinstellungenConf . "
            ORDER BY nSort", 2
    );

    $oSetValue = Shop::DB()->query(
        "SELECT cWert
            FROM teinstellungen
            WHERE kEinstellungenSektion = " . (int)$oConfig_arr[$i]->kEinstellungenSektion . "
                AND cName = '" . $oConfig_arr[$i]->cWertName . "'", 1
    );

    $oConfig_arr[$i]->gesetzterWert = (isset($oSetValue->cWert)) ? $oSetValue->cWert : null;
}

$oBlaetterNaviAktiv      = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, gibAnzahlAktiverGeschenke(), $nAnzahlProSeite);
$oBlaetterNaviHaeufig    = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite2, gibAnzahlHaeufigGekaufteGeschenke(), $nAnzahlProSeite);
$oBlaetterNaviLetzten100 = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite3, gibAnzahlLetzten100Geschenke(), $nAnzahlProSeite);

$smarty->assign('oBlaetterNaviAktiv', $oBlaetterNaviAktiv)
       ->assign('oBlaetterNaviHaeufig', $oBlaetterNaviHaeufig)
       ->assign('oBlaetterNaviLetzten100', $oBlaetterNaviLetzten100)
       ->assign('oAktiveGeschenk_arr', holeAktiveGeschenke($oBlaetterNaviConf->cSQL1))
       ->assign('oHaeufigGeschenk_arr', holeHaeufigeGeschenke($oBlaetterNaviConf->cSQL2))
       ->assign('oLetzten100Geschenk_arr', holeLetzten100Geschenke($oBlaetterNaviConf->cSQL3))
       ->assign('oConfig_arr', $oConfig_arr)
       ->assign('ART_ATTRIBUT_GRATISGESCHENKAB', ART_ATTRIBUT_GRATISGESCHENKAB)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cfehler)
       ->display('gratisgeschenk.tpl');
