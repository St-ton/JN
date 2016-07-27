<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('EMAILHISTORY_VIEW', true, true);

$cHinweis          = '';
$cFehler           = '';
$step              = 'uebersicht';
$nAnzahlProSeite   = 30;
$oEmailhistory     = new Emailhistory();
$cAction           = (isset($_POST['a']) && validateToken()) ? $_POST['a'] : '';

if ($cAction === 'delete') {
    if (isset($_POST['kEmailhistory']) && is_array($_POST['kEmailhistory']) && count($_POST['kEmailhistory']) > 0) {
        $oEmailhistory->deletePack($_POST['kEmailhistory']);
        $cHinweis = 'Ihre markierten Logbucheintr&auml;ge wurden erfolgreich gel&ouml;scht.';
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens einen Logbucheintrag.';
    }
}

if ($step === 'uebersicht') {
    $oPagination = (new Pagination())
        ->setItemCount($oEmailhistory->getCount())
        ->assemble();
    $oEmailhistory_arr = $oEmailhistory->getAll($oPagination->getLimitSQL());
    $smarty->assign('oPagination', $oPagination)
           ->assign('oEmailhistory_arr', $oEmailhistory_arr);
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('step', $step)
       ->display('emailhistory.tpl');
