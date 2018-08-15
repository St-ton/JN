<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

defined('JTLCRON') || define('JTLCRON', true);
if (!defined('PFAD_LOGFILES')) {
    require __DIR__ . '/globalinclude.php';
}
if (PHP_SAPI !== 'cli') {
    while (ob_get_level()) {
        ob_end_clean();
    }
    ignore_user_abort(true);
    header('Connection: close');
    ob_start();
    echo 'Starting cron';
    $size = ob_get_length();
    header('Content-Length: ' . $size);
    ob_end_flush();
    flush();
}
define('JOBQUEUE_LOCKFILE', PFAD_LOGFILES . 'jobqueue.lock');

if (file_exists(JOBQUEUE_LOCKFILE) === false) {
    touch(JOBQUEUE_LOCKFILE);
}

$lockfile = fopen(JOBQUEUE_LOCKFILE, 'rb');

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (PHP_SAPI === 'cli') {
    $handler = new StreamHandler('php://stdout', Logger::DEBUG);
    $handler->setFormatter(new LineFormatter("[%datetime%] %message% %context%\n", null, false, true));
    $logger = new Logger('cron', [$handler]);
} else {
    $logger = Shop::Container()->getLogService();
}

if (flock($lockfile, LOCK_EX | LOCK_NB) === false) {
    $logger->debug('Cron currently locked');
    exit;
}
$db = Shop::Container()->getDB();

$factory = new \Cron\JobFactory($db, $logger);
$queue   = new \Cron\Queue($db, $logger, $factory);
$checker = new \Cron\Checker($db, $logger);

$unqueuedJobs = $checker->check();
$queue->enqueueCronJobs($unqueuedJobs);
$queue->loadQueueFromDB();
$queue->run();

if (file_exists(JOBQUEUE_LOCKFILE)) {
    fclose($lockfile);
    unlink(JOBQUEUE_LOCKFILE);
}
