<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('EMAILHISTORY_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis        = '';
$cFehler         = '';
$step            = 'uebersicht';
$nAnzahlProSeite = 30;
$oEmailhistory   = new Emailhistory();
$cAction         = (isset($_POST['a']) && FormHelper::validateToken()) ? $_POST['a'] : '';

if ($cAction === 'delete') {
    if (isset($_POST['remove_all'])) {
        if (true !== $oEmailhistory->deleteAll()) {
            // 'true' signalizes 'something went wrong during DB-query'
            $cFehler = 'Fehler: eMail-History konnte nicht gelöscht werden!';
        }
    } elseif (isset($_POST['kEmailhistory']) && is_array($_POST['kEmailhistory']) && count($_POST['kEmailhistory']) > 0) {
        $oEmailhistory->deletePack($_POST['kEmailhistory']);
        $cHinweis = 'Ihre markierten Logbucheinträge wurden erfolgreich gelöscht.';
    } else {
        $cFehler = 'Fehler: Bitte markieren Sie mindestens einen Logbucheintrag.';
    }
}

if ($step === 'uebersicht') {
    $oPagination = (new Pagination('emailhist'))
        ->setItemCount($oEmailhistory->getCount())
        ->assemble();
    $smarty->assign('oPagination', $oPagination)
           ->assign('oEmailhistory_arr', $oEmailhistory->getAll(' LIMIT ' . $oPagination->getLimitSQL()));
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('step', $step)
       ->display('emailhistory.tpl');
