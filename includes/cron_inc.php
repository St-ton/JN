<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
defined('JTLCRON') || define('JTLCRON', true);
if (!defined('PFAD_LOGFILES')) {
    require __DIR__ . '/globalinclude.php';
}

define('JOBQUEUE_LOCKFILE', PFAD_LOGFILES . 'jobqueue.lock');

if (file_exists(JOBQUEUE_LOCKFILE) === false) {
    touch(JOBQUEUE_LOCKFILE);
}

$lockfile = fopen(JOBQUEUE_LOCKFILE, 'rb');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

if (PHP_SAPI === 'cli') {
    $logger  = new Logger('cron');
    $handler = new StreamHandler('php://stdout', Logger::DEBUG);
    $handler->setFormatter(new LineFormatter("[%datetime%] %message% %context%\n", null, false, true));
    $logger->pushHandler($handler);
} else {
    $logger = Shop::Container()->getBackendLogService();
}

if (flock($lockfile, LOCK_EX | LOCK_NB) === false) {
    Jtllog::cronLog('Cron currently locked', 2);
    exit;
}

$db = Shop::Container()->getDB();

$factory = new \Cron\JobFactory($db, $logger);
$queue   = new \Cron\Queue($db, $factory, $logger);
$checker = new \Cron\Checker($db, $logger);

$unqueuedJobs = $checker->check();
$queue->enqueueCronJobs($unqueuedJobs);
$queue->loadQueueFromDB();

$queue->run();

if (file_exists(JOBQUEUE_LOCKFILE)) {
    fclose($lockfile);
    unlink(JOBQUEUE_LOCKFILE);
}
