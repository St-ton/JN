<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('MODULE_SAVED_BASKETS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.WarenkorbPers.php';

$cHinweis          = '';
$cFehler           = '';
$step              = 'uebersicht';
$settingsIDs       = array(540);
$cSucheSQL         = new stdClass();
$cSucheSQL->cJOIN  = '';
$cSucheSQL->cWHERE = '';
$nAnzahlProSeite   = 15;
$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(2, $nAnzahlProSeite);
// Tabs
if (strlen(verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', verifyGPDataString('tab'));
}
// Suche
if (strlen(verifyGPDataString('cSuche')) > 0) {
    $cSuche = StringHandler::filterXSS(verifyGPDataString('cSuche'));

    if (strlen($cSuche) > 0) {
        $cSucheSQL->cWHERE = " WHERE (tkunde.cKundenNr LIKE '%" . $cSuche . "%' OR tkunde.cVorname LIKE '%" . $cSuche . "%' OR tkunde.cMail LIKE '%" . $cSuche . "%')";
    }

    $smarty->assign('cSuche', $cSuche);
}
// Einstellungen
if (isset($_POST['einstellungen']) && intval($_POST['einstellungen']) === 1) {
    if (isset($_POST['speichern']) || isset($_POST['a']) && $_POST['a'] === 'speichern') {
        $step = 'uebersicht';
        $cHinweis .= saveAdminSettings($settingsIDs, $_POST);
        $smarty->assign('tab', 'einstellungen');
    }
}

if (isset($_GET['l']) && intval($_GET['l']) > 0 && validateToken()) {
    $kKunde         = intval($_GET['l']);
    $oWarenkorbPers = new WarenkorbPers($kKunde);

    if ($oWarenkorbPers->entferneSelf()) {
        $cHinweis .= 'Ihr ausgew&auml;hlter Warenkorb wurde erfolgreich gel&ouml;scht.';
    }

    unset($oWarenkorbPers);
}

// Gespeicherte Warenkoerbe
$oKunde_arr = Shop::DB()->query(
    "SELECT tkunde.kKunde, tkunde.cFirma, tkunde.cVorname, tkunde.cNachname, DATE_FORMAT(twarenkorbpers.dErstellt, '%d.%m.%Y  %H:%i') AS Datum, count(twarenkorbperspos.kWarenkorbPersPos) AS nAnzahl
        FROM tkunde
        JOIN twarenkorbpers ON tkunde.kKunde = twarenkorbpers.kKunde
        JOIN twarenkorbperspos ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
        " . $cSucheSQL->cWHERE . "
        GROUP BY tkunde.kKunde
        ORDER BY twarenkorbpers.dErstellt DESC" . $oBlaetterNaviConf->cSQL1, 2
);

$oKundeAnzahl = Shop::DB()->query(
    "SELECT count(*) AS nAnzahl
        FROM
        (
            SELECT tkunde.kKunde
            FROM tkunde
            JOIN twarenkorbpers ON tkunde.kKunde = twarenkorbpers.kKunde
            JOIN twarenkorbperspos ON twarenkorbperspos.kWarenkorbPers = twarenkorbpers.kWarenkorbPers
            " . $cSucheSQL->cWHERE . "
            GROUP BY tkunde.kKunde
        ) AS tAnzahl", 1
);

if (is_array($oKunde_arr) && count($oKunde_arr) > 0) {
    foreach ($oKunde_arr as $i => $oKunde) {
        $oKundeTMP = new Kunde($oKunde->kKunde);

        $oKunde_arr[$i]->cNachname = $oKundeTMP->cNachname;
        $oKunde_arr[$i]->cFirma    = $oKundeTMP->cFirma;
    }
}

$smarty->assign('oKunde_arr', $oKunde_arr);

// Anzeigen
if (isset($_GET['a']) && intval($_GET['a']) > 0) {
    $step   = 'anzeigen';
    $kKunde = intval($_GET['a']);

    $oWarenkorbPers = Shop::DB()->query(
        "SELECT count(*) AS nAnzahl
            FROM twarenkorbperspos
            JOIN twarenkorbpers ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
            WHERE twarenkorbpers.kKunde = " . $kKunde, 1
    );
    $oWarenkorbPersPos_arr = Shop::DB()->query(
        "SELECT tkunde.kKunde AS kKundeTMP, tkunde.cVorname, tkunde.cNachname, twarenkorbperspos.kArtikel, twarenkorbperspos.cArtikelName, twarenkorbpers.kKunde,
            twarenkorbperspos.fAnzahl, DATE_FORMAT(twarenkorbperspos.dHinzugefuegt, '%d.%m.%Y  %H:%i') AS Datum
            FROM twarenkorbpers
            JOIN tkunde ON tkunde.kKunde = twarenkorbpers.kKunde
            JOIN twarenkorbperspos ON twarenkorbpers.kWarenkorbPers = twarenkorbperspos.kWarenkorbPers
            WHERE twarenkorbpers.kKunde = " . $kKunde . $oBlaetterNaviConf->cSQL2, 2
    );

    if (is_array($oWarenkorbPersPos_arr) && count($oWarenkorbPersPos_arr) > 0) {
        foreach ($oWarenkorbPersPos_arr as $i => $oWarenkorbPersPos) {
            $oKundeTMP = new Kunde($oWarenkorbPersPos->kKundeTMP);

            $oWarenkorbPersPos_arr[$i]->cNachname = $oKundeTMP->cNachname;
            $oWarenkorbPersPos_arr[$i]->cFirma    = $oKundeTMP->cFirma;
        }
    }

    $smarty->assign('oWarenkorbPersPos_arr', $oWarenkorbPersPos_arr);
    // Baue Blaetternavigation
    $oBlaetterNavi = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite2, $oWarenkorbPers->nAnzahl, $nAnzahlProSeite);
    $smarty->assign('kKunde', $kKunde)
           ->assign('oBlaetterNavi', $oBlaetterNavi);
} else {
    // uebersicht
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
    $oBlaetterNaviKunde = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, $oKundeAnzahl->nAnzahl, $nAnzahlProSeite);

    $smarty->assign('oBlaetterNaviKunde', $oBlaetterNaviKunde)
           ->assign('oConfig_arr', $oConfig_arr);
}

$smarty->assign('step', $step)
       ->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->display('warenkorbpers.tpl');
