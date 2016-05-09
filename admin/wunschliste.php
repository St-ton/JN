<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('MODULE_WISHLIST_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';

$cHinweis          = '';
$settingsIDs       = array(442, 443, 440, 439, 445, 446, 1460);
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
// An Freunde versendete Wunschzettel
$CWunschlisteVersand_arr = Shop::DB()->query(
    "SELECT tkunde.kKunde, tkunde.cNachname, tkunde.cVorname, twunschlisteversand.nAnzahlArtikel, twunschliste.kWunschliste,
            twunschliste.cName, twunschliste.cURLID, twunschlisteversand.nAnzahlEmpfaenger,
            DATE_FORMAT(twunschlisteversand.dZeit, '%d.%m.%Y  %H:%i') AS Datum
        FROM twunschliste
        JOIN twunschlisteversand ON twunschliste.kWunschliste = twunschlisteversand.kWunschliste
        LEFT JOIN tkunde ON twunschliste.kKunde = tkunde.kKunde
        ORDER BY twunschlisteversand.dZeit DESC" . $oBlaetterNaviConf->cSQL3, 2
);
$oWunschlisteFreunde = Shop::DB()->query(
    "SELECT count(*) AS nAnzahl
        FROM twunschliste
        JOIN twunschlisteversand ON twunschliste.kWunschliste = twunschlisteversand.kWunschliste", 1
);
// cNachname entschluesseln
if (is_array($CWunschlisteVersand_arr) && count($CWunschlisteVersand_arr) > 0) {
    foreach ($CWunschlisteVersand_arr as $i => $CWunschlisteVersand) {
        $oKunde = new Kunde($CWunschlisteVersand->kKunde);

        $CWunschlisteVersand_arr[$i]->cNachname = $oKunde->cNachname;
    }
}
// Letzten 100 Wunschzettel mit mindestens einer Position:
$CWunschliste_arr = Shop::DB()->query(
    "SELECT tkunde.kKunde, tkunde.cNachname, tkunde.cVorname, twunschliste.kWunschliste, twunschliste.cName,
        twunschliste.cURLID, DATE_FORMAT(twunschliste.dErstellt, '%d.%m.%Y %H:%i') AS Datum, twunschliste.nOeffentlich,
        count(twunschlistepos.kWunschliste) AS Anzahl
        FROM twunschliste
        JOIN twunschlistepos ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
        LEFT JOIN tkunde ON twunschliste.kKunde = tkunde.kKunde
        GROUP BY twunschliste.kWunschliste
        ORDER BY twunschliste.dErstellt DESC" . $oBlaetterNaviConf->cSQL1, 2
);
$oWunschlistePos = Shop::DB()->query(
    "SELECT count(tWunsch.kWunschliste) AS nAnzahl
        FROM
        (
            SELECT twunschliste.kWunschliste
            FROM twunschliste
            JOIN twunschlistepos ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
            GROUP BY twunschliste.kWunschliste
        ) AS tWunsch", 1
);
if (is_array($CWunschliste_arr) && count($CWunschliste_arr) > 0) {
    foreach ($CWunschliste_arr as $i => $CWunschliste) {
        $oKunde = new Kunde($CWunschliste->kKunde);

        $CWunschliste_arr[$i]->cNachname = $oKunde->cNachname;
    }
}
// Top 100 Artikel auf Wunschzettel
$CWunschlistePos_arr = Shop::DB()->query(
    "SELECT kArtikel, cArtikelName, count(kArtikel) AS Anzahl,
        DATE_FORMAT(dHinzugefuegt, '%d.%m.%Y %H:%i') AS Datum
        FROM twunschlistepos
        GROUP BY kArtikel
        ORDER BY Anzahl DESC" . $oBlaetterNaviConf->cSQL2, 2
);
$oWunschlisteArtikel = Shop::DB()->query(
    "SELECT count(*) AS nAnzahl
        FROM twunschlistepos", 1
);
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
// Baue Blaetternavigation
$oBlaetterNaviPos     = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, $oWunschlistePos->nAnzahl, $nAnzahlProSeite);
$oBlaetterNaviArtikel = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite2, $oWunschlisteArtikel->nAnzahl, $nAnzahlProSeite);
$oBlaetterNaviFreunde = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite3, $oWunschlisteFreunde->nAnzahl, $nAnzahlProSeite);

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('oBlaetterNaviPos', $oBlaetterNaviPos)
       ->assign('oBlaetterNaviArtikel', $oBlaetterNaviArtikel)
       ->assign('oBlaetterNaviFreunde', $oBlaetterNaviFreunde)
       ->assign('CWunschlisteVersand_arr', $CWunschlisteVersand_arr)
       ->assign('CWunschliste_arr', $CWunschliste_arr)
       ->assign('CWunschlistePos_arr', $CWunschlistePos_arr)
       ->assign('hinweis', $cHinweis)
       ->display('wunschliste.tpl');
