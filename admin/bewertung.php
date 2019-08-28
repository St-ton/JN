<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Request;
use JTL\Review\ReviewAdminController;
use JTL\Shop;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_VOTESYSTEM_VIEW', true, true);

require_once PFAD_ROOT . PFAD_INCLUDES . 'bewertung_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bewertung_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$cache       = Shop::Container()->getCache();
$alertHelper = Shop::Container()->getAlertService();
$db          = Shop::Container()->getDB();
$controller  = new ReviewAdminController($db, $cache, $alertHelper, $smarty);
$tab         = mb_strlen(Request::verifyGPDataString('tab')) > 0 ? Request::verifyGPDataString('tab') : 'freischalten';

setzeSprache();
$step = $controller->handleRequest();
if (Request::getVar('a') === 'editieren' || $step === 'bewertung_editieren') {
    $step = 'bewertung_editieren';
    $smarty->assign('review', $controller->getReview(Request::verifyGPCDataInt('kBewertung')));
    if (Request::verifyGPCDataInt('nFZ') === 1) {
        $smarty->assign('nFZ', 1);
    }
} elseif ($step === 'bewertung_uebersicht') {
    $controller->getOverview();
}
$smarty->assign('step', $step)
    ->assign('cTab', $tab)
    ->display('bewertung.tpl');
