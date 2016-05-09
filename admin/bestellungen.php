<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('ORDER_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bestellungen_inc.php';

$cHinweis          = '';
$cFehler           = '';
$step              = 'bestellungen_uebersicht';
$cSuchFilter       = '';
$nAnzahlProSeite   = 15;
$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(1, $nAnzahlProSeite);

// Bestellung Wawi Abholung zuruecksetzen
if (verifyGPCDataInteger('zuruecksetzen') === 1 && validateToken()) {
    switch (setzeAbgeholtZurueck($_POST['kBestellung'])) {
        case -1: // Alles O.K.
            $cHinweis = 'Ihr markierten Bestellungen wurden erfolgreich zur&uuml;ckgesetzt.';
            break;
        case 1:  // Array mit Keys nicht vorhanden oder leer
            $cFehler = 'Fehler: Bitte markieren Sie mindestens eine Bestellung.';
            break;
    }
} elseif (verifyGPCDataInteger('Suche') === 1) { // Bestellnummer gesucht
    $cSuche = StringHandler::filterXSS(verifyGPDataString('cSuche'));
    if (strlen($cSuche) > 0) {
        $cSuchFilter = $cSuche;
    } else {
        $cFehler = 'Fehler: Bitte geben Sie eine Bestellnummer ein.';
    }
}

if ($step === 'bestellungen_uebersicht') {
    $oBlaetterNaviUebersicht = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, gibAnzahlBestellungen($cSuchFilter), $nAnzahlProSeite);
    $smarty->assign('oBlaetterNaviUebersicht', $oBlaetterNaviUebersicht)
           ->assign('oBestellung_arr', gibBestellungsUebersicht($oBlaetterNaviConf->cSQL1, $cSuchFilter));
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cSuche', $cSuchFilter)
       ->assign('cFehler', $cFehler)
       ->assign('step', $step)
       ->display('bestellungen.tpl');
