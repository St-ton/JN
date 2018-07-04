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

if (flock($lockfile, LOCK_EX | LOCK_NB) === false) {
    Jtllog::cronLog('Cron currently locked', 2);
    exit;
}

$db = Shop::Container()->getDB();

$queue   = new \Cron\Queue($db);

//Shop::dbg($queue->getJobs(), false, 'existing jobs:');
$factory = new \Cron\JobFactory();
$checker = new \Cron\Checker($db, Shop::Container()->getBackendLogService(), $factory);

$newJobs = $checker->check();
$queue->setJobs($newJobs);
$saved = $queue->saveToDatabase();
$queue->loadJobsFromDB();
$queue->addJobs($newJobs);
defined('JTLCRON') || define('JTLCRON', true);
$queue->runJobs();

if (file_exists(JOBQUEUE_LOCKFILE)) {
    fclose($lockfile);
    unlink(JOBQUEUE_LOCKFILE);
}
