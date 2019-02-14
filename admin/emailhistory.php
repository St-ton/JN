<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Emailhistory;
use JTL\Helpers\Form;
use JTL\Pagination\Pagination;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('EMAILHISTORY_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */
$cHinweis        = '';
$cFehler         = '';
$step            = 'uebersicht';
$nAnzahlProSeite = 30;
$oEmailhistory   = new Emailhistory();
$cAction         = (isset($_POST['a']) && Form::validateToken()) ? $_POST['a'] : '';

if ($cAction === 'delete') {
    if (isset($_POST['remove_all'])) {
        if ($oEmailhistory->deleteAll() !== true) {
            $cFehler = __('errorHistoryDelete');
        }
    } elseif (isset($_POST['kEmailhistory'])
        && is_array($_POST['kEmailhistory'])
        && count($_POST['kEmailhistory']) > 0
    ) {
        $oEmailhistory->deletePack($_POST['kEmailhistory']);
        $cHinweis = __('successHistoryDelete');
    } else {
        $cFehler = __('errorSelectEntry');
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
