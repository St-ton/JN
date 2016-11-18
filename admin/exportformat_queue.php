<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

global $smarty;

$oAccount->permission('EXPORT_SCHEDULE_VIEW', true, true);

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_queue_inc.php';

if (isset($_GET['action'])) {
    $action = array($_GET['action'] => 1);
} else {
    $action = isset($_POST['action']) ? $_POST['action'] : array('uebersicht' => 1);
}

$step     = 'uebersicht';
$messages = array(
    'notice' => '',
    'error'  => '',
);
if (isset($action['erstellen']) && intval($action['erstellen']) === 1 && validateToken()) {
    $step = exportformatQueueActionErstellen($smarty, $messages);
}
if (isset($action['editieren']) && intval($action['editieren']) === 1 && validateToken()) {
    $step = exportformatQueueActionEditieren($smarty, $messages);
}
if (isset($action['loeschen']) && intval($action['loeschen']) === 1 && validateToken()) {
    $step = exportformatQueueActionLoeschen($smarty, $messages);
}
if (isset($action['triggern']) && intval($action['triggern']) === 1 && validateToken()) {
    $step = exportformatQueueActionTriggern($smarty, $messages);
}
if (isset($action['fertiggestellt']) && intval($action['fertiggestellt']) === 1 && validateToken()) {
    $step = exportformatQueueActionFertiggestellt($smarty, $messages);
}
if (isset($action['erstellen_eintragen']) && intval($action['erstellen_eintragen']) === 1 && validateToken()) {
    $step = exportformatQueueActionErstellenEintragen($smarty, $messages);
}

exportformatQueueFinalize($step, $smarty, $messages);
