<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_ARTICLEOVERLAYS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suchspecialoverlay_inc.php';
/** @global Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$step     = 'suchspecialoverlay_uebersicht';

setzeSprache();
if (RequestHelper::verifyGPCDataInt('suchspecialoverlay') === 1) {
    $step = 'suchspecialoverlay_detail';
    $oID  = RequestHelper::verifyGPCDataInt('kSuchspecialOverlay');

    if (isset($_POST['speicher_einstellung'])
        && (int)$_POST['speicher_einstellung'] === 1
        && FormHelper::validateToken()
    ) {
        if (speicherEinstellung($oID, $_POST, $_FILES)) {
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
            $cHinweis .= 'Ihre Einstellung wurde erfolgreich gespeichert.<br />';
        } else {
            $cFehler .= 'Fehler: Bitte füllen Sie alle Felder komplett aus.<br />';
        }
    }
    // Hole bestimmtes SuchspecialOverlay
    if ($oID > 0) {
        $smarty->assign('oSuchspecialOverlay', gibSuchspecialOverlay($oID));
    }
} else {
    $smarty->assign('oSuchspecialOverlay', gibSuchspecialOverlay(1));
}
$oSuchspecialOverlay_arr = gibAlleSuchspecialOverlays();
$nMaxFileSize            = getMaxFileSize(ini_get('upload_max_filesize'));
$template = Template::getInstance();
if ($template->name === 'Evo' && $template->author === 'JTL-Software-GmbH' && (int)$template->version >= 4) {
    $smarty->assign('isDeprecated', true);
}

$smarty->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('cRnd', time())
       ->assign('nMaxFileSize', $nMaxFileSize)
       ->assign('oSuchspecialOverlay_arr', $oSuchspecialOverlay_arr)
       ->assign('nSuchspecialOverlayAnzahl', count($oSuchspecialOverlay_arr) + 1)
       ->assign('PFAD_SUCHSPECIALOVERLAY', PFAD_SUCHSPECIALOVERLAY_NORMAL)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('suchspecialoverlay.tpl');
