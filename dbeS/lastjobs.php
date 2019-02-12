<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\FileSystem;

require_once __DIR__ . '/syncinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'sprachfunktionen.php';

if (auth()) {
    Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);
    if (!KEEP_SYNC_FILES) {
        \Helpers\FileSystem::delDirRecursively(PFAD_ROOT . PFAD_DBES_TMP);
    }

    \dbeS\LastJob::getInstance()->finishStdJobs();

    $cError = '';
    $jobs   = getJobs();
    $conf   = Shop::getSettings([CONF_GLOBAL, CONF_RSS, CONF_SITEMAP]);
    foreach ($jobs as $oLastJob) {
        switch ((int)$oLastJob->nJob) {
            case LASTJOBS_BEWERTUNGSERINNNERUNG:
                $recipients = (new \ReviewReminder())->getRecipients();
                foreach($recipients as $recipient) {
                    sendeMail(MAILTEMPLATE_BEWERTUNGERINNERUNG, $recipient);
                }
                updateJob(LASTJOBS_BEWERTUNGSERINNNERUNG);
                break;
            case LASTJOBS_SITEMAP:
                if ($conf['sitemap']['sitemap_wawiabgleich'] === 'Y') {
                    $db           = Shop::Container()->getDB();
                    $config       = Shop::getSettings([CONF_GLOBAL, CONF_SITEMAP]);
                    $exportConfig = new \Sitemap\Config\DefaultConfig(
                        $db,
                        $config,
                        Shop::getURL() . '/',
                        Shop::getImageBaseURL()
                    );
                    $exporter     = new \Sitemap\Export(
                        $db,
                        Shop::Container()->getLogService(),
                        new \Sitemap\ItemRenderers\DefaultRenderer(),
                        new \Sitemap\SchemaRenderers\DefaultSchemaRenderer(),
                        $config
                    );
                    $exporter->generate(
                        [Kundengruppe::getDefaultGroupID()],
                        Sprache::getAllLanguages(),
                        $exportConfig->getFactories()
                    );
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

    return \dbeS\LastJob::getInstance()->getRepeatedJobs($GLOBALS['nIntervall']);
}

/**
 * Setzt das dErstellt Datum neu auf die aktuelle Zeit
 *
 * @param int $nJob
 * @return bool
 */
function updateJob($nJob)
{
    return \dbeS\LastJob::getInstance()->restartJob($nJob);
}
