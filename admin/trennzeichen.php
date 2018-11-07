<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('SETTINGS_SEPARATOR_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'trennzeichen_inc.php';
/** @global Smarty\JTLSmarty $smarty */
setzeSprache();

$cHinweis = '';
$cFehler  = '';
$step     = 'trennzeichen_uebersicht';
if (RequestHelper::verifyGPCDataInt('save') === 1 && FormHelper::validateToken()) {
    $oPlausiTrennzeichen = new PlausiTrennzeichen();
    $oPlausiTrennzeichen->setPostVar($_POST);
    $oPlausiTrennzeichen->doPlausi();

    $xPlausiVar_arr = $oPlausiTrennzeichen->getPlausiVar();
    if (count($xPlausiVar_arr) === 0) {
        if (speicherTrennzeichen($_POST)) {
            $cHinweis = 'Ihre Einstellungen wurden erfolgreich gespeichert.';
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_CORE]);
        } else {
            $cFehler = 'Fehler: Ihr Einstellungen konnten nicht gespeichert werden!';
            $smarty->assign('xPostVar_arr', $oPlausiTrennzeichen->getPostVar());
        }
    } else {
        $cFehler = 'Fehler: Bitte füllen Sie alle Pflichtangaben aus!';
        if (isset($xPlausiVar_arr['nDezimal_' . JTL_SEPARATOR_WEIGHT])
            && $xPlausiVar_arr['nDezimal_' . JTL_SEPARATOR_WEIGHT] === 2
        ) {
            $cFehler = 'Fehler: Die Anzahl der Dezimalstellen beim Gewicht dürfen nicht größer 4 sein!';
        }
        if (isset($xPlausiVar_arr['nDezimal_' . JTL_SEPARATOR_AMOUNT])
            && $xPlausiVar_arr['nDezimal_' . JTL_SEPARATOR_AMOUNT] === 2
        ) {
            $cFehler = 'Fehler: Die Anzahl der Dezimalstellen bei der Menge dürfen nicht größer 2 sein!';
        }
        $smarty->assign('xPlausiVar_arr', $oPlausiTrennzeichen->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiTrennzeichen->getPostVar());
    }
}

$smarty->assign('cHinweis', $cHinweis)
       ->assign('cFehler', $cFehler)
       ->assign('step', $step)
       ->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('oTrennzeichenAssoc_arr', Trennzeichen::getAll($_SESSION['kSprache']))
       ->display('trennzeichen.tpl');
