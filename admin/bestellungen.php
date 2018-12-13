<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FormHelper;
use Helpers\RequestHelper;
use Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bestellungen_inc.php';
/** @global \Smarty\JTLSmarty $smarty */
$oAccount->permission('ORDER_VIEW', true, true);

$cHinweis        = '';
$cFehler         = '';
$step            = 'bestellungen_uebersicht';
$cSuchFilter     = '';
$nAnzahlProSeite = 15;

// Bestellung Wawi Abholung zuruecksetzen
if (RequestHelper::verifyGPCDataInt('zuruecksetzen') === 1 && FormHelper::validateToken()) {
    if (isset($_POST['kBestellung'])) {
        switch (setzeAbgeholtZurueck($_POST['kBestellung'])) {
            case -1: // Alles O.K.
                $cHinweis = 'Ihr markierten Bestellungen wurden erfolgreich zurückgesetzt.';
                break;
            case 1:  // Array mit Keys nicht vorhanden oder leer
                $cFehler = 'Fehler: Bitte markieren Sie mindestens eine Bestellung.';
                break;
        }
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens eine Bestellung.';
    }
} elseif (RequestHelper::verifyGPCDataInt('Suche') === 1) { // Bestellnummer gesucht
    $cSuche = StringHandler::filterXSS(RequestHelper::verifyGPDataString('cSuche'));
    if (strlen($cSuche) > 0) {
        $cSuchFilter = $cSuche;
    } else {
        $cFehler = 'Fehler: Bitte geben Sie eine Bestellnummer ein.';
    }
}

if ($step === 'bestellungen_uebersicht') {
    $oPagination     = (new Pagination('bestellungen'))
        ->setItemCount(gibAnzahlBestellungen($cSuchFilter))
        ->assemble();
    $oBestellung_arr = gibBestellungsUebersicht(' LIMIT ' . $oPagination->getLimitSQL(), $cSuchFilter);
    $smarty->assign('oBestellung_arr', $oBestellung_arr)
           ->assign('oPagination', $oPagination);
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cSuche', $cSuchFilter)
       ->assign('cFehler', $cFehler)
       ->assign('step', $step)
       ->display('bestellungen.tpl');
