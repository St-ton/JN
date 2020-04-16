<?php

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Template;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('DISPLAY_ARTICLEOVERLAYS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'suchspecialoverlay_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$alertHelper = Shop::Container()->getAlertService();
$step        = 'suchspecialoverlay_uebersicht';

setzeSprache();
if (Request::verifyGPCDataInt('suchspecialoverlay') === 1) {
    $step = 'suchspecialoverlay_detail';
    $oID  = Request::verifyGPCDataInt('kSuchspecialOverlay');
    if (Request::postInt('speicher_einstellung') === 1
        && Form::validateToken()
        && speicherEinstellung($oID, $_POST, $_FILES['cSuchspecialOverlayBild'])
    ) {
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_OPTION, CACHING_GROUP_ARTICLE]);
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successConfigSave'), 'successConfigSave');
    }
    if ($oID > 0) {
        $smarty->assign('oSuchspecialOverlay', gibSuchspecialOverlay($oID));
    }
} else {
    $smarty->assign('oSuchspecialOverlay', gibSuchspecialOverlay(1));
}
$overlays    = gibAlleSuchspecialOverlays();
$maxFileSize = getMaxFileSize(ini_get('upload_max_filesize'));
$template    = Template::getInstance();
if ($template->name === 'Evo' && $template->author === 'JTL-Software-GmbH' && (int)$template->version >= 4) {
    $smarty->assign('isDeprecated', true);
}

$smarty->assign('cRnd', time())
       ->assign('nMaxFileSize', $maxFileSize)
       ->assign('oSuchspecialOverlay_arr', $overlays)
       ->assign('nSuchspecialOverlayAnzahl', count($overlays) + 1)
       ->assign('PFAD_SUCHSPECIALOVERLAY', PFAD_SUCHSPECIALOVERLAY_NORMAL)
       ->assign('step', $step)
       ->display('suchspecialoverlay.tpl');
