<?php declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use JTL\Backend\Permissions;
use JTL\Cron\Checker;
use JTL\Cron\Job\Export;
use JTL\Cron\JobFactory;
use JTL\Cron\JobHydrator;
use JTL\Cron\Queue;
use JTL\Cron\Type;
use JTL\Export\ExporterFactory;
use JTL\Export\Model;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class ExportQueueController
 * @package JTL\Router\Controller\Backend
 */
class ExportQueueController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::EXPORT_SCHEDULE_VIEW);
        $this->getText->loadAdminLocale('pages/exportformat_queue');

        $action   = isset($_GET['action'])
            ? [$_GET['action'] => 1]
            : ($_POST['action'] ?? ['uebersicht' => 1]);
        $step     = 'uebersicht';
        $messages = [
            'notice' => '',
            'error'  => ''
        ];
        if (Form::validateToken()) {
            if (isset($action['erstellen']) && (int)$action['erstellen'] === 1) {
                $step = $this->stepCreate($smarty);
            }
            if (isset($action['editieren']) && (int)$action['editieren'] === 1) {
                $step = $this->stepEdit($smarty, $messages);
            }
            if (isset($action['loeschen']) && (int)$action['loeschen'] === 1) {
                $step = $this->stepDelete($messages);
            }
            if (isset($action['triggern']) && (int)$action['triggern'] === 1) {
                $step = $this->stepTrigger($messages);
            }
            if (isset($action['fertiggestellt']) && (int)$action['fertiggestellt'] === 1) {
                $step = $this->stepDone($smarty);
            }
            if (isset($action['erstellen_eintragen']) && (int)$action['erstellen_eintragen'] === 1) {
                $step = $this->stepCreateInsert($smarty, $messages);
            }
        }

        $result = $this->exportformatQueueFinalize($step, $smarty, $messages);

        return $result ?? $smarty->assign('step', $step)
                ->assign('route', $this->route)
                ->getResponse('exportformat_queue.tpl');
    }

    /**
     * @return stdClass[]
     */
    private function holeExportformatCron(): array
    {
        $exports = $this->db->getObjects(
            "SELECT texportformat.*, tcron.cronID, tcron.frequency, tcron.startDate, 
            DATE_FORMAT(tcron.startDate, '%d.%m.%Y %H:%i') AS dStart_de, tcron.lastStart, 
            DATE_FORMAT(tcron.lastStart, '%d.%m.%Y %H:%i') AS dLetzterStart_de,
            DATE_FORMAT(COALESCE(tcron.nextStart, tcron.startDate), '%d.%m.%Y %H:%i')
            AS dNaechsterStart_de
            FROM texportformat
            JOIN tcron 
                ON tcron.jobType = 'exportformat'
                AND tcron.foreignKeyID = texportformat.kExportformat
            ORDER BY tcron.startDate DESC"
        );
        $factory = new ExporterFactory($this->db, Shop::Container()->getLogService(), $this->cache);
        foreach ($exports as $export) {
            $export->kExportformat      = (int)$export->kExportformat;
            $export->kKundengruppe      = (int)$export->kKundengruppe;
            $export->kSprache           = (int)$export->kSprache;
            $export->kWaehrung          = (int)$export->kWaehrung;
            $export->kKampagne          = (int)$export->kKampagne;
            $export->kPlugin            = (int)$export->kPlugin;
            $export->nSpecial           = (int)$export->nSpecial;
            $export->nVarKombiOption    = (int)$export->nVarKombiOption;
            $export->nSplitgroesse      = (int)$export->nSplitgroesse;
            $export->nUseCache          = (int)$export->nUseCache;
            $export->nFehlerhaft        = (int)$export->nFehlerhaft;
            $export->cronID             = (int)$export->cronID;
            $export->frequency          = (int)$export->frequency;
            $export->cAlleXStdToDays    = $this->getFrequency($export->frequency);
            $export->frequencyLocalized = $export->cAlleXStdToDays;

            $exporter = $factory->getExporter($export->kExportformat);
            $exporter->init($export->kExportformat);
            try {
                $export->Sprache = Shop::Lang()->getLanguageByID($export->kSprache);
            } catch (Exception) {
                $export->Sprache = LanguageHelper::getDefaultLanguage();
                $export->Sprache->setLocalizedName('???');
                $export->Sprache->setId(0);
                $export->nFehlerhaft = 1;
            }
            $export->Waehrung     = $this->db->select(
                'twaehrung',
                'kWaehrung',
                $export->kWaehrung
            );
            $export->Kundengruppe = $this->db->select(
                'tkundengruppe',
                'kKundengruppe',
                $export->kKundengruppe
            );
            $export->oJobQueue    = $this->db->getSingleObject(
                "SELECT *, DATE_FORMAT(lastStart, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_de 
                    FROM tjobqueue 
                    WHERE cronID = :id",
                ['id' => $export->cronID]
            );
            $export->productCount = $exporter->getExportProductCount();
        }

        return $exports;
    }

    /**
     * @param int $cronID
     * @return int|stdClass
     * @former holeCron()
     */
    private function getCron(int $cronID): int|stdClass
    {
        $cron = $this->db->getSingleObject(
            "SELECT *, DATE_FORMAT(tcron.startDate, '%d.%m.%Y %H:%i') AS dStart_de
                FROM tcron
                WHERE cronID = :cid",
            ['cid' => $cronID]
        );
        if ($cron !== null && $cron->cronID > 0) {
            $cron->cronID       = (int)$cron->cronID;
            $cron->frequency    = (int)$cron->frequency;
            $cron->foreignKeyID = (int)($cron->foreignKeyID ?? 0);

            return $cron;
        }

        return 0;
    }

    /**
     * @param int $hours
     * @return bool|string
     * @former rechneUmAlleXStunden()
     */
    private function getFrequency(int $hours): bool|string
    {
        if ($hours <= 0) {
            return false;
        }
        if ($hours > 24) {
            $res = \round($hours / 24);
            if ($res >= 365) {
                $res /= 365;
                if ($res === 1.0) {
                    $res .= \__('year');
                } else {
                    $res .= \__('years');
                }
            } elseif ($res === 1.0) {
                $res .= \__('day');
            } else {
                $res .= \__('days');
            }
        } elseif ($hours > 1) {
            $res = $hours . \__('hours');
        } else {
            $res = $hours . \__('hour');
        }

        return $res;
    }

    /**
     * @return Model[]
     * @former holeAlleExportformate()
     */
    private function getExports(): array
    {
        $data    = $this->db->getInts(
            'SELECT kExportformat 
                FROM texportformat
                ORDER BY cName, kSprache, kKundengruppe, kWaehrung',
            'kExportformat'
        );
        $formats = [];
        foreach ($data as $id) {
            $formats[] = Model::loadByAttributes(['id' => $id], $this->db);
        }

        return $formats;
    }

    /**
     * @param int    $exportID
     * @param string $start
     * @param int    $freq
     * @param int    $cronID
     * @return int
     * @former erstelleExportformatCron()
     */
    private function createCron(int $exportID, string $start, int $freq, int $cronID = 0): int
    {
        if ($exportID <= 0 || $freq < 1 || !$this->checkStartTime($start)) {
            return 0;
        }
        if ($cronID > 0) {
            // Editieren
            $this->db->queryPrepared(
                'DELETE tcron, tjobqueue
                    FROM tcron
                    LEFT JOIN tjobqueue 
                        ON tjobqueue.cronID = tcron.cronID
                    WHERE tcron.cronID = :id',
                ['id' => $cronID]
            );
            $job = new Export($this->db, Shop::Container()->getLogService(), new JobHydrator(), $this->cache);
            $job->setID($cronID);
            $job->setName('export' . $start . '_' . $exportID);
            $job->setFrequency($freq);
            $job->setStartDate($this->formatDate($start));
            $job->setStartTime($this->formatDate($start, true));
            $job->setForeignKey('kExportformat');
            $job->setForeignKeyID($exportID);
            $job->setTableName('texportformat');
            $job->setNextStartDate($this->formatDate($start));
            $job->setType(Type::EXPORT);
            $job->insert();

            return 1;
        }
        // Pruefe ob Exportformat nicht bereits vorhanden
        $cron = $this->db->select(
            'tcron',
            'foreignKey',
            'kExportformat',
            'foreignKeyID',
            $exportID
        );
        if ($cron !== null && $cron->cronID > 0) {
            return -1;
        }
        $job = new Export($this->db, Shop::Container()->getLogService(), new JobHydrator(), $this->cache);
        $job->setName('export' . $start . '_' . $exportID);
        $job->setFrequency($freq);
        $job->setStartDate($this->formatDate($start));
        $job->setStartTime($this->formatDate($start, true));
        $job->setForeignKey('kExportformat');
        $job->setForeignKeyID($exportID);
        $job->setTableName('texportformat');
        $job->setNextStartDate($this->formatDate($start));
        $job->setType(Type::EXPORT);
        $job->insert();

        return 1;
    }

    /**
     * @param string $start
     * @return bool
     * @former dStartPruefen()
     */
    private function checkStartTime(string $start): bool
    {
        if (\preg_match('/^([0-3]\d[.][0-1]\d[.]\d{4} [0-2]\d:[0-6]\d)/', $start)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $dateStart
     * @param bool   $asTime
     * @return string
     */
    private function formatDate(string $dateStart, bool $asTime = false): string
    {
        [$date, $time]        = \explode(' ', $dateStart);
        [$day, $month, $year] = \explode('.', $date);

        return $asTime ? $time : $year . '-' . $month . '-' . $day . ' ' . $time;
    }

    /**
     * @param int[]|string[] $cronIDs
     * @return bool
     * @former loescheExportformatCron()
     */
    private function deleteCron(array $cronIDs): bool
    {
        foreach (\array_map('\intval', $cronIDs) as $cronID) {
            $this->db->delete('tjobqueue', 'cronID', $cronID);
            $this->db->delete('tcron', 'cronID', $cronID);
        }

        return true;
    }

    /**
     * @param int $hours
     * @return stdClass[]
     * @former holeExportformatQueueBearbeitet()
     */
    private function getQueues(int $hours = 24): array
    {
        $languageID = (int)($_SESSION['editLanguageID'] ?? 0);
        if (!$languageID) {
            $languageID = LanguageHelper::getDefaultLanguage()->getId();
        }
        $languages = LanguageHelper::getAllLanguages(1);
        $queues    = $this->db->getObjects(
            "SELECT texportformat.cName, texportformat.cDateiname, texportformatqueuebearbeitet.*,
            DATE_FORMAT(texportformatqueuebearbeitet.dZuletztGelaufen, '%d.%m.%Y %H:%i') AS dZuletztGelaufen_DE,
            tsprache.cNameDeutsch AS cNameSprache, tkundengruppe.cName AS cNameKundengruppe,
            twaehrung.cName AS cNameWaehrung
            FROM texportformatqueuebearbeitet
            JOIN texportformat
                ON texportformat.kExportformat = texportformatqueuebearbeitet.kExportformat
            JOIN tsprache
                ON tsprache.kSprache = texportformat.kSprache
            JOIN tkundengruppe
                ON tkundengruppe.kKundengruppe = texportformat.kKundengruppe
            JOIN twaehrung
                ON twaehrung.kWaehrung = texportformat.kWaehrung
            WHERE DATE_SUB(NOW(), INTERVAL :hrs HOUR) < texportformatqueuebearbeitet.dZuletztGelaufen
            ORDER BY texportformatqueuebearbeitet.dZuletztGelaufen DESC",
            ['hrs' => $hours]
        );
        foreach ($queues as $exportFormat) {
            $exportFormat->name      = $languages[$languageID]->getLocalizedName();
            $exportFormat->kJobQueue = (int)$exportFormat->kJobQueue;
            $exportFormat->nLimitN   = (int)$exportFormat->nLimitN;
            $exportFormat->nLimitM   = (int)$exportFormat->nLimitM;
            $exportFormat->nInArbeit = (int)$exportFormat->nInArbeit;
        }

        return $queues;
    }

    /**
     * @param JTLSmarty $smarty
     * @return string
     */
    private function stepCreate(JTLSmarty $smarty): string
    {
        $smarty->assign('exportFormats', $this->getExports());

        return 'erstellen';
    }

    /**
     * @param JTLSmarty $smarty
     * @param array     $messages
     * @return string
     * @former exportformatQueueActionEditieren()
     */
    private function stepEdit(JTLSmarty $smarty, array &$messages): string
    {
        $id   = Request::verifyGPCDataInt('kCron');
        $cron = $id > 0 ? $this->getCron($id) : 0;
        if (\is_object($cron) && $cron->cronID > 0) {
            $step = 'erstellen';
            $smarty->assign('cron', $cron)
                ->assign('exportFormats', $this->getExports());
        } else {
            $messages['error'] .= \__('errorWrongQueue');
            $step               = 'uebersicht';
        }

        return $step;
    }

    /**
     * @param array $messages
     * @return string
     * @former exportformatQueueActionLoeschen()
     */
    private function stepDelete(array &$messages): string
    {
        $cronIDs = $_POST['kCron'] ?? [];
        if (\is_array($cronIDs) && \count($cronIDs) > 0) {
            if ($this->deleteCron($cronIDs)) {
                $messages['notice'] .= \__('successQueueDelete');
            } else {
                $messages['error'] .= \__('errorUnknownLong') . '<br />';
            }
        } else {
            $messages['error'] .= \__('errorWrongQueue');
        }

        return 'loeschen_result';
    }

    /**
     * @param array $messages
     * @return string
     * @former exportformatQueueActionTriggern()
     */
    private function stepTrigger(array &$messages): string
    {
        global $bCronManuell;
        $bCronManuell = true;

        $logger = Shop::Container()->getLogService();
        $runner = new Queue($this->db, $logger, new JobFactory($this->db, $logger, $this->cache));
        $res    = $runner->run(new Checker($this->db, $logger));
        if ($res === -1) {
            $messages['error'] .= \__('errorCronLocked') . '<br />';
        } elseif ($res === 0) {
            $messages['error'] .= \__('errorCronStart') . '<br />';
        } elseif ($res === 1) {
            $messages['notice'] .= \__('successCronStart') . '<br />';
        } elseif ($res > 1) {
            $messages['notice'] .= \sprintf(\__('successCronsStart'), $res) . '<br />';
        }

        return 'triggern';
    }

    /**
     * @param JTLSmarty $smarty
     * @return string
     * @former exportformatQueueActionFertiggestellt()
     */
    private function stepDone(JTLSmarty $smarty): string
    {
        $hours = Request::verifyGPCDataInt('nStunden');
        if ($hours <= 0) {
            $hours = 24;
        }

        $_SESSION['exportformatQueue.nStunden'] = $hours;
        $smarty->assign('cTab', 'fertig');

        return 'fertiggestellt';
    }

    /**
     * @param JTLSmarty $smarty
     * @param array     $messages
     * @return string
     * @former exportformatQueueActionErstellenEintragen()
     */
    private function stepCreateInsert(JTLSmarty $smarty, array &$messages): string
    {
        $id                   = Request::postInt('kExportformat');
        $start                = $_POST['dStart'] ?? '';
        $freq                 = !empty($_POST['nAlleXStundenCustom'])
            ? (int)$_POST['nAlleXStundenCustom']
            : (int)$_POST['nAlleXStunden'];
        $error                = new stdClass();
        $error->kExportformat = $id;
        $error->dStart        = Text::filterXSS($_POST['dStart']);
        $error->nAlleXStunden = $freq;
        if ($id > 0) {
            if ($this->checkStartTime($start)) {
                if ($freq >= 1) {
                    $state = $this->createCron($id, $start, $freq, Request::postInt('kCron'));
                    if ($state === 1) {
                        $messages['notice'] .= \__('successQueueCreate');
                        $step                = 'erstellen_success';
                    } elseif ($state === -1) {
                        $messages['error'] .= \__('errorFormatInQueue') . '<br />';
                        $step               = 'erstellen';
                    } else {
                        $messages['error'] .= \__('errorUnknownLong') . '<br />';
                        $step               = 'erstellen';
                    }
                } else { // Alle X Stunden ist entweder leer oder kleiner als 6
                    $messages['error'] .= \__('errorGreaterEqualOne') . '<br />';
                    $step               = 'erstellen';
                    $smarty->assign('error', $error);
                }
            } else { // Kein gueltiges Datum + Uhrzeit
                $messages['error'] .= \__('errorEnterValidDate') . '<br />';
                $step               = 'erstellen';
                $smarty->assign('error', $error);
            }
        } else { // Kein gueltiges Exportformat
            $messages['error'] .= \__('errorFormatSelect') . '<br />';
            $step               = 'erstellen';
            $smarty->assign('error', $error);
        }

        return $step;
    }

    /**
     * @param string     $tab
     * @param array|null $messages
     * @return ResponseInterface
     */
    private function exportformatQueueRedirect(string $tab = '', array $messages = null): ResponseInterface
    {
        if (isset($messages['notice']) && !empty($messages['notice'])) {
            $_SESSION['exportformatQueue.notice'] = $messages['notice'];
        } else {
            unset($_SESSION['exportformatQueue.notice']);
        }
        if (isset($messages['error']) && !empty($messages['error'])) {
            $_SESSION['exportformatQueue.error'] = $messages['error'];
        } else {
            unset($_SESSION['exportformatQueue.error']);
        }

        $urlParams = null;
        if (!empty($tab)) {
            $urlParams['tab'] = Text::filterXSS($tab);
        }

        return new RedirectResponse(
            $this->baseURL . $this->route
            . (\is_array($urlParams) ? '?' . \http_build_query($urlParams, '', '&') : '')
        );
    }

    /**
     * @param string    $step
     * @param JTLSmarty $smarty
     * @param array     $messages
     * @return ResponseInterface|null
     */
    private function exportformatQueueFinalize(string $step, JTLSmarty $smarty, array &$messages): ?ResponseInterface
    {
        if (isset($_SESSION['exportformatQueue.notice'])) {
            $messages['notice'] = $_SESSION['exportformatQueue.notice'];
            unset($_SESSION['exportformatQueue.notice']);
        }
        if (isset($_SESSION['exportformatQueue.error'])) {
            $messages['error'] = $_SESSION['exportformatQueue.error'];
            unset($_SESSION['exportformatQueue.error']);
        }

        switch ($step) {
            case 'uebersicht':
                $freq = (int)($_SESSION['exportformatQueue.nStunden'] ?? 24);
                $smarty->assign('oExportformatCron_arr', $this->holeExportformatCron())
                    ->assign('oExportformatQueueBearbeitet_arr', $this->getQueues($freq))
                    ->assign('nStunden', $freq);
                break;
            case 'erstellen_success':
            case 'loeschen_result':
            case 'triggern':
                return $this->exportformatQueueRedirect('aktiv', $messages);
            case 'fertiggestellt':
                return $this->exportformatQueueRedirect('fertig', $messages);
            case 'erstellen':
                if (!empty($messages['error'])) {
                    $freq = (int)($_SESSION['exportformatQueue.nStunden'] ?? 24);
                    $smarty->assign('oExportformatCron_arr', $this->holeExportformatCron())
                        ->assign('oExportformatQueueBearbeitet_arr', $this->getQueues($freq))
                        ->assign('exportFormats', $this->getExports())
                        ->assign('nStunden', $freq);
                }
                break;
            default:
                break;
        }

        $this->alertService->addError($messages['error'], 'expoFormatError');
        $this->alertService->addNotice($messages['notice'], 'expoFormatNote');

        return null;
    }
}
