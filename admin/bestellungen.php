<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bestellungen_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$oAccount->permission('ORDER_VIEW', true, true);

$cHinweis        = '';
$cFehler         = '';
$step            = 'bestellungen_uebersicht';
$cSuchFilter     = '';
$nAnzahlProSeite = 15;

// Bestellung Wawi Abholung zuruecksetzen
if (Request::verifyGPCDataInt('zuruecksetzen') === 1 && Form::validateToken()) {
    if (isset($_POST['kBestellung'])) {
        switch (setzeAbgeholtZurueck($_POST['kBestellung'])) {
            case -1: // Alles O.K.
                $cHinweis = __('successOrderReset');
                break;
            case 1:  // Array mit Keys nicht vorhanden oder leer
                $cFehler = __('errorAtLeastOneOrder');
                break;
        }
    } else {
        $cFehler = __('errorAtLeastOneOrder');
    }
} elseif (Request::verifyGPCDataInt('Suche') === 1) { // Bestellnummer gesucht
    $cSuche = Text::filterXSS(Request::verifyGPDataString('cSuche'));
    if (mb_strlen($cSuche) > 0) {
        $cSuchFilter = $cSuche;
    } else {
        $cFehler = __('errorMissingOrderNumber');
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
