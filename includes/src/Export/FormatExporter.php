<?php declare(strict_types=1);

namespace JTL\Export;

use DateTime;
use Exception;
use InvalidArgumentException;
use JTL\Cron\QueueEntry;
use JTL\DB\DbInterface;
use JTL\Helpers\Category;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\ExportSmarty;
use PDO;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class FormatExporter
 * @package JTL\Export
 */
class FormatExporter
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var ExportSmarty
     */
    private $smarty;

    /**
     * @var QueueEntry
     */
    private $queue;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FileWriter
     */
    private $fileWriter;

    /**
     * @var float
     */
    private $startedAt;

    /**
     * FormatExporter constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->logger = $logger;
    }

    /**
     * @param int $exportID
     */
    public function init(int $exportID): void
    {
        $this->startedAt = \microtime(true);
        try {
            $this->model = Model::load(['id' => $exportID], $this->db, Model::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Cannot find export with id ' . $exportID);
        }
        $this->setConfig($exportID);
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param int $exportID
     * @return $this
     */
    private function setConfig(int $exportID): self
    {
        $confObj = $this->db->selectAll(
            'texportformateinstellungen',
            'kExportformat',
            $exportID
        );
        foreach ($confObj as $conf) {
            $this->config[$conf->cName] = $conf->cWert;
        }
        $this->config['exportformate_lager_ueber_null'] = $this->config['exportformate_lager_ueber_null'] ?? 'N';
        $this->config['exportformate_preis_ueber_null'] = $this->config['exportformate_preis_ueber_null'] ?? 'N';
        $this->config['exportformate_beschreibung']     = $this->config['exportformate_beschreibung'] ?? 'N';
        $this->config['exportformate_quot']             = $this->config['exportformate_quot'] ?? 'N';
        $this->config['exportformate_equot']            = $this->config['exportformate_equot'] ?? 'N';
        $this->config['exportformate_semikolon']        = $this->config['exportformate_semikolon'] ?? 'N';
        $this->config['exportformate_line_ending']      = $this->config['exportformate_line_ending'] ?? 'LF';

        return $this;
    }

    /**
     * @return string
     */
    private function getNewLine(): string
    {
        return ($this->config['exportformate_line_ending'] ?? 'LF') === 'LF' ? "\n" : "\r\n";
    }

    /**
     * @param QueueEntry $queue
     * @return $this
     */
    private function setQueue(QueueEntry $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * @return QueueEntry|null
     */
    public function getQueue(): ?QueueEntry
    {
        return $this->queue;
    }

    /**
     * @return $this
     */
    private function initSmarty(): self
    {
        $this->smarty = new ExportSmarty($this->db);
        $this->smarty->assign('URL_SHOP', Shop::getURL())
            ->assign('Waehrung', Frontend::getCurrency())
            ->assign('Einstellungen', $this->getConfig());

        return $this;
    }

    /**
     * @param bool $countOnly
     * @return string
     */
    private function getExportSQL(bool $countOnly = false): string
    {
        $where = '';
        $join  = '';
        $limit = '';
        switch ($this->model->getVarcombOption()) {
            case 2:
                $where = ' AND kVaterArtikel = 0';
                break;
            case 3:
                $where = ' AND (tartikel.nIstVater != 1 OR tartikel.kEigenschaftKombi > 0)';
                break;
            default:
                break;
        }
        if ($this->config['exportformate_lager_ueber_null'] === 'Y') {
            $where .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y'))";
        } elseif ($this->config['exportformate_lager_ueber_null'] === 'O') {
            $where .= " AND (NOT (tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten = 'Y') 
                            OR tartikel.cLagerKleinerNull = 'Y')";
        }

        if ($this->config['exportformate_preis_ueber_null'] === 'Y') {
            $join .= ' JOIN tpreis ON tpreis.kArtikel = tartikel.kArtikel
                                AND tpreis.kKundengruppe = ' . $this->model->getCustomerGroupID() . '
                          JOIN tpreisdetail ON tpreisdetail.kPreis = tpreis.kPreis
                                AND tpreisdetail.nAnzahlAb = 0
                                AND tpreisdetail.fVKNetto > 0';
        }

        if ($this->config['exportformate_beschreibung'] === 'Y') {
            $where .= " AND tartikel.cBeschreibung != ''";
        }

        $condition = 'AND (tartikel.dErscheinungsdatum IS NULL OR NOT (DATE(tartikel.dErscheinungsdatum) > CURDATE()))';
        $conf      = Shop::getSettings([\CONF_GLOBAL]);
        if (($conf['global']['global_erscheinende_kaeuflich'] ?? 'N') === 'Y') {
            $condition = "AND (
                tartikel.dErscheinungsdatum IS NULL 
                OR NOT (DATE(tartikel.dErscheinungsdatum) > CURDATE())
                OR  (
                        DATE(tartikel.dErscheinungsdatum) > CURDATE()
                        AND (tartikel.cLagerBeachten = 'N' 
                            OR tartikel.fLagerbestand > 0 OR tartikel.cLagerKleinerNull = 'Y')
                    )
            )";
        }

        if ($countOnly === true) {
            $select = 'COUNT(*) AS nAnzahl';
        } else {
            $queue  = $this->getQueue();
            $select = 'tartikel.kArtikel';
            $limit  = ' ORDER BY tartikel.kArtikel';
            if ($queue !== null) {
                $limit     .= ' LIMIT ' . $queue->taskLimit;
                $condition .= ' AND tartikel.kArtikel > ' . $this->getQueue()->lastProductID;
            }
        }

        return 'SELECT ' . $select . "
            FROM tartikel
            LEFT JOIN tartikelattribut ON tartikelattribut.kArtikel = tartikel.kArtikel
                AND tartikelattribut.cName = '" . \FKT_ATTRIBUT_KEINE_PREISSUCHMASCHINEN . "'
            " . $join . '
            LEFT JOIN tartikelsichtbarkeit ON tartikelsichtbarkeit.kArtikel = tartikel.kArtikel
                AND tartikelsichtbarkeit.kKundengruppe = ' . $this->model->getCustomerGroupID() . '
            WHERE tartikelattribut.kArtikelAttribut IS NULL' . $where . '
                AND tartikelsichtbarkeit.kArtikel IS NULL ' . $condition . $limit;
    }

    /**
     * @return int
     */
    public function getExportProductCount(): int
    {
        $sql = $this->getExportSQL(true);
        $cid = 'xp_' . \md5($sql);
        if (($count = Shop::Container()->getCache()->get($cid)) !== false) {
            return $count ?? 0;
        }
        $count = (int)$this->db->getSingleObject($sql)->nAnzahl;
        Shop::Container()->getCache()->set($cid, $count, [\CACHING_GROUP_CORE], 120);

        return $count;
    }

    /**
     * @param DateTime|string $lastCreated
     * @return $this
     */
    public function setZuletztErstellt($lastCreated): self
    {
        $this->model->setDateLastCreated($lastCreated);

        return $this;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function update(): int
    {
        return $this->model->save() === true ? 1 : 0;
    }

    /**
     * @return int
     */
    private function getTotalCount(): int
    {
        return (int)$this->db->getSingleObject($this->getExportSQL(true))->nAnzahl;
    }

    /**
     * @param int        $exportID
     * @param QueueEntry $queueEntry
     * @param bool       $isAsync
     * @param bool       $back
     * @param bool       $isCron
     * @param int|null   $max
     * @return bool
     * @throws InvalidArgumentException
     */
    public function startExport(
        int $exportID,
        QueueEntry $queueEntry,
        bool $isAsync = false,
        bool $back = false,
        bool $isCron = false,
        int $max = null
    ): bool {
        $this->init($exportID);
        $this->setQueue($queueEntry);
        $max           = $max ?? $this->getTotalCount();
        $started       = false;
        $pseudoSession = new Session();
        $pseudoSession->initSession($this->model, $this->db);
        $this->initSmarty();
        if ($this->model->getPluginID() > 0 && \mb_strpos($this->model->getContent(), \PLUGIN_EXPORTFORMAT_CONTENTFILE) !== false) {
            $this->startPluginExport($isCron, $isAsync, $queueEntry, $max);
            if ($queueEntry->jobQueueID > 0 && empty($queueEntry->cronID)) {
                $this->db->delete('texportqueue', 'kExportqueue', $queueEntry->jobQueueID);
            }
            $this->quit();
            $this->logger->notice('Finished export');

            return !$started;
        }
        $cacheHits    = 0;
        $cacheMisses  = 0;
        $output       = '';
        $errorMessage = '';

        $this->fileWriter = new FileWriter($this->smarty, $this->model, $this->config);

        if ((int)$this->queue->tasksExecuted === 0) {
            $this->fileWriter->deleteOldTempFile();
        }
        try {
            $this->fileWriter->start();
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage());
            if ($isAsync) {
                $cb = new AsyncCallback();
                $cb->setExportID($this->model->getId())
                    ->setQueueID($this->queue->jobQueueID)
                    ->setError($e->getMessage());
                $this->finish($cb, $isAsync, $back);
                exit();
            }

            return false;
        }

        $this->logger->notice('Starting exportformat "' . Text::convertUTF8($this->model->getName())
            . '" for language ' . $this->model->getLanguageID()
            . ' and customer group ' . $this->model->getCustomerGroupID()
            . ' with caching ' . ((Shop::Container()->getCache()->isActive() && $this->model->getUseCache())
                ? 'enabled'
                : 'disabled')
            . ' - ' . $queueEntry->tasksExecuted . '/' . $max . ' products exported');
        if ((int)$this->queue->tasksExecuted === 0) {
            $this->fileWriter->writeHeader();
        }
        $fallback     = (\mb_strpos($this->model->getContent(), '->oKategorie_arr') !== false);
        $options      = Product::getExportOptions();
        $helper       = Category::getInstance($this->model->getLanguageID(), $this->model->getCustomerGroupID());
        $shopURL      = Shop::getURL();
        $imageBaseURL = Shop::getImageBaseURL();
        $res          = $this->db->getPDOStatement($this->getExportSQL());
        while (($productData = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $product = new Product($this->db);
            $product->fuelleArtikel(
                (int)$productData->kArtikel,
                $options,
                $this->model->getCustomerGroupID(),
                $this->model->getLanguageID(),
                !$this->model->getUseCache()
            );
            if ($product->kArtikel <= 0) {
                continue;
            }
            $product->kSprache                 = $this->model->getLanguageID();
            $product->kKundengruppe            = $this->model->getCustomerGroupID();
            $product->kWaehrung                = $this->model->getCurrencyID();
            $product->campaignValue            = $this->model->getCampaignValue();
            $product->currencyConversionFactor = $pseudoSession->getCurrency()->getConversionFactor();

            $started = true;
            ++$this->queue->tasksExecuted;
            $this->queue->lastProductID = $product->kArtikel;
            if ($product->cacheHit === true) {
                ++$cacheHits;
            } else {
                ++$cacheMisses;
            }
            $product = $product->augmentProduct($this->config);
            $product->addCategoryData($fallback);
            $product->Kategoriepfad = $product->Kategorie->cKategoriePfad ?? $helper->getPath($product->Kategorie);
            $product->cDeeplink     = $shopURL . '/' . $product->cURL;
            $product->Artikelbild   = $product->Bilder[0]->cPfadGross
                ? $imageBaseURL . $product->Bilder[0]->cPfadGross
                : '';

            $_out = $this->smarty->assign('Artikel', $product)->fetch('db:' . $this->model->getId());
            if (!empty($_out)) {
                $output .= $_out . $this->getNewLine();
            }

            \executeHook(\HOOK_DO_EXPORT_OUTPUT_FETCHED);
            if (!$isAsync && ($queueEntry->tasksExecuted % \max(\round($queueEntry->taskLimit / 10), 10)) === 0) {
                // max. 10 status updates per run
                $this->logger->notice($queueEntry->tasksExecuted . '/' . $max . ' products exported');
            }
        }
        if (\mb_strlen($output) > 0) {
            $this->fileWriter->writeContent($output);
        }

        if ($isCron !== false) {
            $this->finishCronRun($started, (int)$queueEntry->foreignKeyID, $cacheHits, $cacheMisses);
        } else {
            $cb = new AsyncCallback();
            $cb->setExportID($this->model->getId())
                ->setTasksExecuted($this->queue->tasksExecuted)
                ->setQueueID($this->queue->jobQueueID)
                ->setProductCount($max)
                ->setLastProductID($this->queue->lastProductID)
                ->setIsFinished(false)
                ->setIsFirst(((int)$this->queue->tasksExecuted === 0))
                ->setCacheHits($cacheHits)
                ->setCacheMisses($cacheMisses)
                ->setError($errorMessage);
            if ($started === true) {
                // One or more products have been exported
                $this->finishRun($cb, $isAsync);
            } else {
                $this->finish($cb, $isAsync, $back);
            }
        }
        $pseudoSession->restoreSession();

        if ($isAsync) {
            exit();
        }

        return !$started;
    }

    /**
     * @param AsyncCallback $cb
     * @param bool          $isAsync
     * @param bool          $back
     */
    private function finish(AsyncCallback $cb, bool $isAsync, bool $back): void
    {
        // There are no more products to export
        $this->db->queryPrepared(
            'UPDATE texportformat 
                SET dZuletztErstellt = NOW() 
                WHERE kExportformat = :eid',
            ['eid' => $this->model->getId()]
        );
        $this->db->delete('texportqueue', 'kExportqueue', (int)$this->queue->foreignKeyID);

        $this->fileWriter->writeFooter();
        if ($this->fileWriter->finish()) {
            // Versucht (falls so eingestellt) die erstellte Exportdatei in mehrere Dateien zu splitten
            try {
                $this->fileWriter->splitFile();
            } catch (Exception $e) {
                $cb->setError($e->getMessage());
            }
        } else {
            try {
                $errorMessage = \sprintf(
                    \__('Cannot create export file %s. Missing write permissions?'),
                    $this->model->getSanitizedFilepath()
                );
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
            $cb->setError($errorMessage);
        }
        if ($back === true) {
            if ($isAsync) {
                $cb->setIsFinished(true)
                    ->setIsFirst(false)
                    ->output();
            } else {
                \header(
                    'Location: ' . Shop::getAdminURL() . '/exportformate.php?action=exported&token='
                    . $_SESSION['jtl_token']
                    . '&kExportformat=' . $this->model->getId()
                    . '&max=' . $cb->getProductCount()
                    . '&hasError=' . (int)($cb->getError() !== '')
                );
            }
        }
    }

    /**
     * @param AsyncCallback $cb
     * @param bool          $isAsync
     */
    private function finishRun(AsyncCallback $cb, bool $isAsync): void
    {
        $this->fileWriter->close();
        $this->db->queryPrepared(
            'UPDATE texportqueue SET
                nLimit_n       = nLimit_n + :nLimitM,
                nLastArticleID = :nLastArticleID
                WHERE kExportqueue = :kExportqueue',
            [
                'nLimitM'        => $this->queue->taskLimit,
                'nLastArticleID' => $this->queue->lastProductID,
                'kExportqueue'   => (int)$this->queue->jobQueueID,
            ]
        );
        if ($isAsync) {
            $cb->output();
        } else {
            \header(
                'Location: ' . Shop::getAdminURL() . '/do_export.php'
                . '?e=' . (int)$this->queue->jobQueueID
                . '&back=admin&token=' . $_SESSION['jtl_token']
                . '&max=' . $cb->getProductCount()
            );
        }
    }

    /**
     * @param bool $started
     * @param int  $exportID
     * @param int  $cacheHits
     * @param int  $cacheMisses
     */
    private function finishCronRun(bool $started, int $exportID, int $cacheHits, int $cacheMisses): void
    {
        // finalize job when there are no more products to export
        if ($started === false) {
            $this->logger->notice('Finalizing job...');
            $this->db->update(
                'texportformat',
                'kExportformat',
                $exportID,
                (object)['dZuletztErstellt' => 'NOW()']
            );
            $this->fileWriter->deleteOldExports();
            $this->fileWriter->writeFooter();
            $this->fileWriter->finish();
            $this->fileWriter->splitFile();
        }
        $this->logger->notice('Finished after ' . \round(\microtime(true) - $this->startedAt, 4)
            . 's. Product cache hits: ' . $cacheHits
            . ', misses: ' . $cacheMisses);
    }

    /**
     * @param bool       $isCron
     * @param bool       $isAsync
     * @param QueueEntry $queueObject
     * @param int        $max
     * @return bool|void
     */
    private function startPluginExport(bool $isCron, bool $isAsync, QueueEntry $queueObject, int $max)
    {
        $this->logger->notice('Starting plugin exportformat "' . $this->model->getName()
            . '" for language ' . $this->model->getLanguageID()
            . ' and customer group ' . $this->model->getCustomerGroupID()
            . ' with caching ' . ((Shop::Container()->getCache()->isActive() && $this->model->getUseCache())
                ? 'enabled'
                : 'disabled'));
        $loader = PluginHelper::getLoaderByPluginID($this->model->getPluginID(), $this->db);
        try {
            $oPlugin = $loader->init($this->model->getPluginID());
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            $this->quit(true);

            return false;
        }
        if ($oPlugin->getState() !== State::ACTIVATED) {
            $this->quit(true);
            $this->logger->notice('Plugin disabled');

            return false;
        }
        if ($isCron === true) {
            global $oJobQueue;
            $oJobQueue = $queueObject;
        } else {
            global $queue;
            $queue = $queueObject;
        }
        global $exportformat, $ExportEinstellungen;
        $exportformat                   = new stdClass();
        $exportformat->kKundengruppe    = $this->model->getCustomerGroupID();
        $exportformat->kExportformat    = $this->model->getId();
        $exportformat->kSprache         = $this->model->getLanguageID();
        $exportformat->kWaehrung        = $this->model->getCurrencyID();
        $exportformat->kKampagne        = $this->model->getCampaignID();
        $exportformat->kPlugin          = $this->model->getPluginID();
        $exportformat->cName            = $this->model->getName();
        $exportformat->cDateiname       = $this->model->getFilename();
        $exportformat->cKopfzeile       = $this->model->getHeader();
        $exportformat->cContent         = $this->model->getContent();
        $exportformat->cFusszeile       = $this->model->getFooter();
        $exportformat->cKodierung       = $this->model->getEncoding();
        $exportformat->nSpecial         = $this->model->getIsSpecial();
        $exportformat->nVarKombiOption  = $this->model->getVarcombOption();
        $exportformat->nSplitgroesse    = $this->model->getSplitSize();
        $exportformat->dZuletztErstellt = $this->model->getDateLastCreated();
        $exportformat->nUseCache        = $this->model->getUseCache();
        $exportformat->max              = $max;
        $exportformat->async            = $isAsync;
        // needed by Google Shopping export format plugin
        $exportformat->tkampagne_cParameter = $this->model->getCampaignParameter();
        $exportformat->tkampagne_cWert      = $this->model->getCampaignValue();
        // needed for plugin exports
        $ExportEinstellungen = $this->getConfig();
        include $oPlugin->getPaths()->getExportPath()
            . \str_replace(\PLUGIN_EXPORTFORMAT_CONTENTFILE, '', $this->model->getContent());
        if ($isAsync) {
            $this->model->setDateLastCreated(new DateTime());
            $this->model->save(['dateLastCreated']);
            exit;
        }
    }

    /**
     * @param bool $hasError
     */
    private function quit(bool $hasError = false): void
    {
        if (Request::getVar('back') !== 'admin') {
            return;
        }
        $location = 'Location: ' . Shop::getAdminURL()
            . '/exportformate.php?action=exported&token=' . $_SESSION['jtl_token']
            . '&kExportformat=' . (int)$this->queue->foreignKeyID;
        if ($hasError) {
            $location .= '&hasError=1';
        }
        \header($location);
        exit;
    }
}
