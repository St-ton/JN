<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_ARTICLEOVERLAYS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suchspecialoverlay_inc.php';
/** @global Smarty\JTLSmarty $smarty */
$cHinweis = '';
$cFehler  = '';
$step     = 'suchspecialoverlay_uebersicht';

setzeSprache();
if (Request::verifyGPCDataInt('suchspecialoverlay') === 1) {
    $step = 'suchspecialoverlay_detail';
    $oID  = Request::verifyGPCDataInt('kSuchspecialOverlay');
    if (isset($_POST['speicher_einstellung'])
        && (int)$_POST['speicher_einstellung'] === 1
        && Form::validateToken()
    ) {
        if (speicherEinstellung($oID, $_POST, $_FILES)) {
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
            $cHinweis .= __('successConfigSave') . '<br />';
        } else {
            $cFehler .= __('errorFillRequired') . '<br />';
        }
    }
    if ($oID > 0) {
        $smarty->assign('oSuchspecialOverlay', gibSuchspecialOverlay($oID));
    }
} else {
    $smarty->assign('oSuchspecialOverlay', gibSuchspecialOverlay(1));
}
$searchSpecialOverlays = gibAlleSuchspecialOverlays();
$maxFileSize           = getMaxFileSize(ini_get('upload_max_filesize'));
$template              = Template::getInstance();
if ($template->name === 'Evo' && $template->author === 'JTL-Software-GmbH' && (int)$template->version >= 4) {
    $smarty->assign('isDeprecated', true);
}

$smarty->assign('Sprachen', Sprache::getAllLanguages())
       ->assign('cRnd', time())
       ->assign('nMaxFileSize', $maxFileSize)
       ->assign('oSuchspecialOverlay_arr', $searchSpecialOverlays)
       ->assign('nSuchspecialOverlayAnzahl', count($searchSpecialOverlays) + 1)
       ->assign('PFAD_SUCHSPECIALOVERLAY', PFAD_SUCHSPECIALOVERLAY_NORMAL)
       ->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('suchspecialoverlay.tpl');
