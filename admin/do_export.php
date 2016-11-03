<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

if (!ini_get('safe_mode')) {
    @ini_set('max_execution_time', 0);
}

if (!isset($_GET['e']) || !intval($_GET['e']) > 0 || !validateToken()) {
    die('0');
}

$queue = Shop::DB()->select('texportqueue', 'kExportqueue', (int)$_GET['e']);
if ($queue === false || !isset($queue->kExportformat) || !$queue->kExportformat || !$queue->nLimit_m) {
    die('1');
}
$ef = new Exportformat($queue->kExportformat);
if (!$ef->isOK()) {
    die('2');
}
$ef->startExport($queue, isset($_GET['ajax']), (isset($_GET['back']) && $_GET['back'] === 'admin'), false);
