<?php declare(strict_types=1);

namespace JTL\dbeS;

use JTL\Catalog\ReviewReminder;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\dbeS\Job\JobInterface;
use JTL\Export\RSS;
use JTL\Helpers\FileSystem;
use JTL\Language\LanguageHelper;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;
use JTL\Sitemap\Config\DefaultConfig;
use JTL\Sitemap\Export;
use JTL\Sitemap\ItemRenderers\DefaultRenderer;
use JTL\Sitemap\SchemaRenderers\DefaultSchemaRenderer;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class LastJob
 * @package JTL\dbeS
 */
final class LastJob
{
    /**
     * LastJob constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(private DbInterface $db, private LoggerInterface $logger)
    {
    }

    public function execute(): void
    {
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()');
        if (!\KEEP_SYNC_FILES) {
            FileSystem::delDirRecursively(\PFAD_ROOT . \PFAD_DBES_TMP);
        }
        $this->disableUnsedManufacturers();
        $this->finishStdJobs();
        $GLOBALS['nIntervall'] = \defined('LASTJOBS_INTERVALL') ? \LASTJOBS_INTERVALL : 12;
        $jobs                  = $this->getRepeatedJobs($GLOBALS['nIntervall']);
        \executeHook(\HOOK_LASTJOBS_HOLEJOBS, ['jobs' => &$jobs]);
        $config = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_SITEMAP, \CONF_BEWERTUNG]);
        foreach ($jobs as $job) {
            switch ((int)$job->nJob) {
                case \LASTJOBS_BEWERTUNGSERINNNERUNG:
                    if ($config['bewertung']['bewertung_anzeigen'] !== 'Y') {
                        break;
                    }
                    $recipients = (new ReviewReminder())->getRecipients();
                    $mailer     = Shop::Container()->get(Mailer::class);
                    $mail       = new Mail();
                    foreach ($recipients as $recipient) {
                        $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BEWERTUNGERINNERUNG, $recipient));
                    }
                    $this->restartJob(\LASTJOBS_BEWERTUNGSERINNNERUNG);
                    break;
                case \LASTJOBS_SITEMAP:
                    if ($config['sitemap']['sitemap_wawiabgleich'] !== 'Y') {
                        break;
                    }
                    $exportConfig = new DefaultConfig(
                        $this->db,
                        $config,
                        Shop::getURL() . '/',
                        Shop::getImageBaseURL()
                    );
                    $exporter     = new Export(
                        $this->db,
                        $this->logger,
                        new DefaultRenderer(),
                        new DefaultSchemaRenderer(),
                        $config
                    );
                    $exporter->generate(
                        [CustomerGroup::getDefaultGroupID()],
                        LanguageHelper::getAllLanguages(),
                        $exportConfig->getFactories()
                    );
                    $this->restartJob(\LASTJOBS_SITEMAP);
                    break;
                case \LASTJOBS_RSS:
                    if ($config['rss']['rss_wawiabgleich'] !== 'Y') {
                        break;
                    }
                    $rss = new RSS($this->db, $this->logger);
                    $rss->generateXML();
                    $this->restartJob(\LASTJOBS_RSS);
                    break;
                case \LASTJOBS_GARBAGECOLLECTOR:
                    if ($config['global']['garbagecollector_wawiabgleich'] !== 'Y') {
                        break;
                    }
                    Shop::Container()->getDBServiceGC()->run();
                    $this->restartJob(\LASTJOBS_GARBAGECOLLECTOR);
                    break;
                default:
                    break;
            }
        }
    }

    private function disableUnsedManufacturers(): void
    {
        $this->db->query(
            'UPDATE thersteller
                SET nAktiv = IF(
                    EXISTS (SELECT 1 FROM tartikel WHERE tartikel.kHersteller = thersteller.kHersteller), 1, 0)'
        );
    }

    /**
     * @param int $hours
     * @return stdClass[]
     */
    private function getRepeatedJobs(int $hours): array
    {
        return $this->db->getObjects(
            "SELECT kJob, nJob, dErstellt
                FROM tlastjob
                WHERE cType = 'RPT'
                    AND (dErstellt IS NULL OR DATE_ADD(dErstellt, INTERVAL :hrs HOUR) < NOW())",
            ['hrs' => $hours]
        );
    }

    /**
     * @return stdClass[]
     */
    private function getStdJobs(): array
    {
        return $this->db->selectAll(
            'tlastjob',
            ['cType', 'nFinished'],
            ['STD', 1],
            'kJob, nJob, cJobName, dErstellt',
            'dErstellt'
        );
    }

    /**
     * @param int $jobID
     * @return null|stdClass
     */
    private function getJob(int $jobID): ?stdClass
    {
        return $this->db->select('tlastjob', 'nJob', $jobID);
    }

    /**
     * @param int         $jobID
     * @param string|null $className
     * @return stdClass
     */
    public function run(int $jobID, string $className = null): stdClass
    {
        $job = $this->getJob($jobID);
        if ($job === null) {
            $job       = (object)[
                'cType'     => 'STD',
                'nJob'      => $jobID,
                'cJobName'  => $className,
                'nCounter'  => 1,
                'dErstellt' => \date('Y-m-d H:i:s'),
                'nFinished' => 0,
            ];
            $job->kJob = $this->db->insert('tlastjob', $job);
        } else {
            $job->nCounter++;
            $job->dErstellt = \date('Y-m-d H:i:s');

            $this->db->update('tlastjob', 'kJob', $job->kJob, $job);
        }

        return $job;
    }

    /**
     * @param int $jobID
     */
    private function restartJob(int $jobID): void
    {
        $this->db->update(
            'tlastjob',
            'nJob',
            $jobID,
            (object)[
                'nCounter'  => 0,
                'dErstellt' => \date('Y-m-d H:i:s'),
                'nFinished' => 0,
            ]
        );
    }

    /**
     * @return void
     */
    private function finishStdJobs(): void
    {
        $keys    = ['cType', 'nFinished'];
        $keyVals = ['STD', 0];
        $this->db->update('tlastjob', $keys, $keyVals, (object)['nFinished' => 1]);

        $keyVals[1] = 1;
        $jobs       = $this->getStdJobs();
        foreach ($jobs as $job) {
            $class = $job->cJobName;
            $full  = 'JTL\\dbeS\\Job\\' . $class;
            if (!\class_exists($full) || !\in_array(JobInterface::class, \class_implements($full), true)) {
                continue;
            }
            /** @var JobInterface $instance */
            $instance = new $full($this->db, Shop::Container()->getCache());
            $instance->run();
        }
        $this->db->delete('tlastjob', $keys, $keyVals);
    }
}
