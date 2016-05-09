<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('EMAILHISTORY_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'blaetternavi.php';

$cHinweis          = '';
$cFehler           = '';
$step              = 'uebersicht';
$nAnzahlProSeite   = 30;
$oBlaetterNaviConf = baueBlaetterNaviGetterSetter(1, $nAnzahlProSeite);
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
    $oBlaetterNaviUebersicht = baueBlaetterNavi($oBlaetterNaviConf->nAktuelleSeite1, $oEmailhistory->getCount(), $nAnzahlProSeite);
    $smarty->assign('oBlaetterNaviUebersicht', $oBlaetterNaviUebersicht)
           ->assign('oEmailhistory_arr', $oEmailhistory->getAll($oBlaetterNaviConf->cSQL1));
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('step', $step)
       ->display('emailhistory.tpl');
