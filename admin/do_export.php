<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;

require_once __DIR__ . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'exportformat_inc.php';

@ini_set('max_execution_time', 0);

if (!isset($_GET['e']) || !((int)$_GET['e'] > 0) || !Form::validateToken()) {
    die('0');
}
$db    = Shop::Container()->getDB();
$queue = $db->select('texportqueue', 'kExportqueue', (int)$_GET['e']);
if (!isset($queue->kExportformat) || !$queue->kExportformat || !$queue->nLimit_m) {
    Shop::dbg($_GET, false, 'GET:');
    Shop::dbg($queue, false, 'Q:');
    die('1');
}
$ef = new Exportformat($queue->kExportformat, $db);
if (!$ef->isOK()) {
    die('2');
}
$queue->jobQueueID    = $queue->kExportqueue;
$queue->cronID        = 0;
$queue->foreignKeyID  = 0;
$queue->taskLimit     = $queue->nLimit_m;
$queue->tasksExecuted = $queue->nLimit_n;
$queue->lastProductID = $queue->nLastArticleID;
$queue->jobType       = 'exportformat';
$queue->tableName     = null;
$queue->foreignKey    = 'kExportformat';
$queue->foreignKeyID  = $queue->kExportformat;

$ef->startExport(
    new \Cron\QueueEntry($queue),
    isset($_GET['ajax']),
    isset($_GET['back']) && $_GET['back'] === 'admin',
    false,
    (isset($_GET['max']) ? (int)$_GET['max'] : null)
);
