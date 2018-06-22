<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_WISHLIST_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis          = '';
$settingsIDs       = [442, 443, 440, 439, 445, 446, 1460];
// Tabs
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
// Einstellungen
if (RequestHelper::verifyGPCDataInt('einstellungen') === 1) {
    $cHinweis .= saveAdminSettings($settingsIDs, $_POST);
}
// Anzahl Wunschzettel, gewünschte Artikel, versendete Wunschzettel
$oWunschlistePos = Shop::Container()->getDB()->query(
    "SELECT count(tWunsch.kWunschliste) AS nAnzahl
        FROM
        (
            SELECT twunschliste.kWunschliste
            FROM twunschliste
            JOIN twunschlistepos 
                ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
            GROUP BY twunschliste.kWunschliste
        ) AS tWunsch",
    \DB\ReturnType::SINGLE_OBJECT
);
$oWunschlisteArtikel = Shop::Container()->getDB()->query(
    "SELECT count(*) AS nAnzahl
        FROM twunschlistepos",
    \DB\ReturnType::SINGLE_OBJECT
);
$oWunschlisteFreunde = Shop::Container()->getDB()->query(
    "SELECT count(*) AS nAnzahl
        FROM twunschliste
        JOIN twunschlisteversand 
            ON twunschliste.kWunschliste = twunschlisteversand.kWunschliste",
    \DB\ReturnType::SINGLE_OBJECT
);
// Paginationen
$oPagiPos = (new Pagination('pos'))
    ->setItemCount($oWunschlistePos->nAnzahl)
    ->assemble();
$oPagiArtikel = (new Pagination('artikel'))
    ->setItemCount($oWunschlisteArtikel->nAnzahl)
    ->assemble();
$oPagiFreunde = (new Pagination('freunde'))
    ->setItemCount($oWunschlisteFreunde->nAnzahl)
    ->assemble();
// An Freunde versendete Wunschzettel
$CWunschlisteVersand_arr = Shop::Container()->getDB()->query(
    "SELECT tkunde.kKunde, tkunde.cNachname, tkunde.cVorname, twunschlisteversand.nAnzahlArtikel, 
        twunschliste.kWunschliste, twunschliste.cName, twunschliste.cURLID, 
        twunschlisteversand.nAnzahlEmpfaenger, DATE_FORMAT(twunschlisteversand.dZeit, '%d.%m.%Y  %H:%i') AS Datum
        FROM twunschliste
        JOIN twunschlisteversand 
            ON twunschliste.kWunschliste = twunschlisteversand.kWunschliste
        LEFT JOIN tkunde 
            ON twunschliste.kKunde = tkunde.kKunde
        ORDER BY twunschlisteversand.dZeit DESC
        LIMIT " . $oPagiFreunde->getLimitSQL(),
    \DB\ReturnType::ARRAY_OF_OBJECTS);
// cNachname entschluesseln
foreach ($CWunschlisteVersand_arr as $i => $CWunschlisteVersand) {
    if ($CWunschlisteVersand->kKunde !== null) {
        $oKunde = new Kunde($CWunschlisteVersand->kKunde);
        $CWunschlisteVersand_arr[$i]->cNachname = $oKunde->cNachname;
    }
}
// Letzten 100 Wunschzettel mit mindestens einer Position:
$CWunschliste_arr = Shop::Container()->getDB()->query(
    "SELECT tkunde.kKunde, tkunde.cNachname, tkunde.cVorname, twunschliste.kWunschliste, twunschliste.cName,
        twunschliste.cURLID, DATE_FORMAT(twunschliste.dErstellt, '%d.%m.%Y %H:%i') AS Datum, 
        twunschliste.nOeffentlich, count(twunschlistepos.kWunschliste) AS Anzahl
        FROM twunschliste
        JOIN twunschlistepos 
            ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
        LEFT JOIN tkunde 
            ON twunschliste.kKunde = tkunde.kKunde
        GROUP BY twunschliste.kWunschliste
        ORDER BY twunschliste.dErstellt DESC
        LIMIT " . $oPagiPos->getLimitSQL(),
    \DB\ReturnType::ARRAY_OF_OBJECTS);
foreach ($CWunschliste_arr as $i => $CWunschliste) {
    if ($CWunschliste->kKunde !== null) {
        $oKunde = new Kunde($CWunschliste->kKunde);
        $CWunschliste_arr[$i]->cNachname = $oKunde->cNachname;
    }
}
// Top 100 Artikel auf Wunschzettel
$CWunschlistePos_arr = Shop::Container()->getDB()->query(
    "SELECT kArtikel, cArtikelName, count(kArtikel) AS Anzahl,
        DATE_FORMAT(dHinzugefuegt, '%d.%m.%Y %H:%i') AS Datum
        FROM twunschlistepos
        GROUP BY kArtikel
        ORDER BY Anzahl DESC
        LIMIT " . $oPagiArtikel->getLimitSQL(),
    \DB\ReturnType::ARRAY_OF_OBJECTS);
// Config holen
$oConfig_arr = Shop::Container()->getDB()->query(
    "SELECT *
        FROM teinstellungenconf
        WHERE kEinstellungenConf IN (" . implode(',', $settingsIDs) . ")
        ORDER BY nSort",
    \DB\ReturnType::ARRAY_OF_OBJECTS
);
$configCount = count($oConfig_arr);
for ($i = 0; $i < $configCount; $i++) {
    $oConfig_arr[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
        'teinstellungenconfwerte',
        'kEinstellungenConf',
        (int)$oConfig_arr[$i]->kEinstellungenConf,
        '*',
        'nSort'
    );
    $oSetValue = Shop::Container()->getDB()->select(
        'teinstellungen',
        'kEinstellungenSektion',
        (int)$oConfig_arr[$i]->kEinstellungenSektion,
        'cName',
        $oConfig_arr[$i]->cWertName
    );
    $oConfig_arr[$i]->gesetzterWert = $oSetValue->cWert ?? null;
}

$smarty->assign('oConfig_arr', $oConfig_arr)
       ->assign('oPagiPos', $oPagiPos)
       ->assign('oPagiArtikel', $oPagiArtikel)
       ->assign('oPagiFreunde', $oPagiFreunde)
       ->assign('CWunschlisteVersand_arr', $CWunschlisteVersand_arr)
       ->assign('CWunschliste_arr', $CWunschliste_arr)
       ->assign('CWunschlistePos_arr', $CWunschlistePos_arr)
       ->assign('hinweis', $cHinweis)
       ->display('wunschliste.tpl');
