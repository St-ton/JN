<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

if (auth()) {
    Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = now()', \DB\ReturnType::DEFAULT);
    if (!KEEP_SYNC_FILES) {
        FileSystemHelper::delDirRecursively(PFAD_ROOT . PFAD_DBES_TMP);
    }

    LastJob::getInstance()->finishStdJobs();

    $cError = '';
    $jobs   = getJobs();
    $conf   = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_SITEMAP]);
    if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
        Jtllog::writeLog('LastJob Job Array: ' .
            print_r($jobs, true), JTLLOG_LEVEL_DEBUG, false, 'LastJob Job Array');
    }
    foreach ($jobs as $oLastJob) {
        if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
            Jtllog::writeLog('Lastjobs Job: ' .
                print_r($oLastJob, true), JTLLOG_LEVEL_DEBUG, false, 'nJob', $oLastJob->nJob);
        }
        switch ((int)$oLastJob->nJob) {
            case LASTJOBS_BEWERTUNGSERINNNERUNG:
                require_once PFAD_ROOT . PFAD_ADMIN . 'includes/bewertungserinnerung.php';
                baueBewertungsErinnerung();
                updateJob(LASTJOBS_BEWERTUNGSERINNNERUNG);
                break;
            case LASTJOBS_SITEMAP:
                if ($conf['sitemap']['sitemap_wawiabgleich'] === 'Y') {
                    require_once PFAD_ROOT . PFAD_ADMIN . 'includes/sitemapexport.php';
                    generateSitemapXML();
                    updateJob(LASTJOBS_SITEMAP);
                }
                break;
            case LASTJOBS_RSS:
                if ($conf['rss']['rss_wawiabgleich'] === 'Y') {
                    require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'rss_inc.php';
                    generiereRSSXML();
                    updateJob(LASTJOBS_RSS);
                }
                break;
            case LASTJOBS_GARBAGECOLLECTOR:
                if ($conf['global']['garbagecollector_wawiabgleich'] === 'Y') {
                    Shop::Container()->getDBServiceGC()->run();
                    updateJob(LASTJOBS_GARBAGECOLLECTOR);
                }
                break;
            default:
                break;
        }
    }
    die('0');
}
if (Jtllog::doLog(JTLLOG_LEVEL_DEBUG)) {
    Jtllog::writeLog('BEENDE: ' . $_FILES['data']['tmp_name'], JTLLOG_LEVEL_DEBUG, false, 'lastjobs');
}
die('3');

/**
 * Hole alle Jobs
 *
 * @return array
 */
function getJobs()
{
    $GLOBALS['nIntervall'] = defined('LASTJOBS_INTERVALL') ? LASTJOBS_INTERVALL : 12;
    executeHook(HOOK_LASTJOBS_HOLEJOBS);

    return LastJob::getInstance()->getRepeatedJobs($GLOBALS['nIntervall']);
}

/**
 * Setzt das dErstellt Datum neu auf die aktuelle Zeit
 *
 * @param int $nJob
 * @return bool
 */
function updateJob($nJob)
{
    return LastJob::getInstance()->restartJob($nJob);
}
