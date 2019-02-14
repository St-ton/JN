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

use JTL\Shop;
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
    $logger->log(JTLLOG_LEVEL_NOTICE, 'Cron currently locked');
    exit;
}

$db      = Shop::Container()->getDB();
$queue   = new \JTL\Cron\Queue($db, $logger, new \JTL\Cron\JobFactory($db, $logger));
$checker = new \JTL\Cron\Checker($db, $logger);

$unqueuedJobs = $checker->check();
$queue->enqueueCronJobs($unqueuedJobs);
$queue->loadQueueFromDB();
$queue->run();

if (file_exists(JOBQUEUE_LOCKFILE)) {
    fclose($lockfile);
    unlink(JOBQUEUE_LOCKFILE);
}
