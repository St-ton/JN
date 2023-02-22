<?php declare(strict_types=1);

namespace JTL\Export;

use DateTime;
use Exception;
use InvalidArgumentException;
use JTL\Cron\QueueEntry;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Category;
use JTL\Helpers\Request;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\ExportSmarty;
use PDO;
use stdClass;

/**
 * Class FormatExporter
 * @package JTL\Export
 */
class FormatExporter extends AbstractExporter
{
    /**
     * @return string
     */
    private function getNewLine(): string
    {
        return ($this->config['exportformate_line_ending'] ?? 'LF') === 'LF' ? "\n" : "\r\n";
    }

    /**
     * @return $this
     */
    private function initSmarty(): self
    {
        $this->smarty = new ExportSmarty($this->db);
        $this->smarty->assign('URL_SHOP', Shop::getURL())
            ->assign('ShopURL', Shop::getURL())
            ->assign('Waehrung', Frontend::getCurrency())
            ->assign('Einstellungen', $this->getConfig());

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExportProductCount(): int
    {
        $sql = $this->getExportSQL(true);
        $cid = 'xp_' . \md5($sql);
        if (($count = $this->getCache()->get($cid)) !== false) {
            return $count ?? 0;
        }
        $count = (int)$this->db->getSingleObject($sql)->nAnzahl;
        $this->getCache()->set($cid, $count, [\CACHING_GROUP_CORE], 120);

        return $count;
    }

    /**
     * @inheritdoc
     */
    public function setZuletztErstellt($lastCreated): self
    {
        $this->model->setDateLastCreated($lastCreated);

        return $this;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
        \executeHook(\HOOK_EXPORT_START, [
            'exporter' => $this,
            'exportID' => $exportID,
            'isAsync'  => $isAsync,
            'isCron'   => $isCron,
            'max'      => &$max
        ]);
        $fileWriterClass = $this->getFileWriterClass();
        $this->writer    = $this->writer ?? new $fileWriterClass($this->model, $this->config, $this->smarty);
        if ($this->model->getPluginID() > 0
            && \str_contains($this->model->getContent(), \PLUGIN_EXPORTFORMAT_CONTENTFILE)
        ) {
            $started = $this->startPluginExport($isCron, $isAsync, $queueEntry, $max);
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

        if ($this->queue->tasksExecuted === 0) {
            $this->writer->deleteOldTempFile();
        }
        try {
            $this->writer->start();
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
        $customerGroup   = CustomerGroup::getByID($this->model->getCustomerGroupID());
        $customerGroupID = $customerGroup->getID();
        $languageID      = $this->model->getLanguageID();
        $currency        = $this->model->getCurrency();

        $this->logger->notice('Starting exportformat "' . $this->model->getName()
            . '" for language ' . $languageID
            . ' and customer group ' . $customerGroupID
            . ' with caching ' . (($this->getCache()->isActive() && $this->model->getUseCache())
                ? 'enabled'
                : 'disabled')
            . ' - ' . $queueEntry->tasksExecuted . '/' . $max . ' products exported');
        if ($this->queue->tasksExecuted === 0) {
            $this->writer->writeHeader();
        }
        $fallback     = \str_contains($this->model->getContent(), '->oKategorie_arr');
        $options      = Product::getExportOptions();
        $shopURL      = Shop::getURL();
        $imageBaseURL = Shop::getImageBaseURL();
        $res          = $this->db->getPDOStatement($this->getExportSQL());
        while (($productData = $res->fetch(PDO::FETCH_OBJ)) !== false) {
            $product = new Product($this->db, $customerGroup, $currency);
            $product->fuelleArtikel(
                (int)$productData->kArtikel,
                $options,
                $customerGroupID,
                $languageID,
                !$this->model->getUseCache()
            );
            if ($product->kArtikel <= 0) {
                continue;
            }
            $product->kSprache                 = $languageID;
            $product->kKundengruppe            = $customerGroupID;
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
            $product = $product->augmentProduct($this->config, $this->model);
            $product->addCategoryData($fallback);
            $product->Kategoriepfad = $product->Kategorie->getCategoryPathString($languageID);
            $product->cDeeplink     = $shopURL . '/' . $product->cURL;
            $product->Artikelbild   = $product->Bilder[0]->cPfadGross
                ? $imageBaseURL . $product->Bilder[0]->cPfadGross
                : '';
            \executeHook(\HOOK_EXPORT_PRE_RENDER, [
                'product'  => $product,
                'exporter' => $this,
                'exportID' => $exportID
            ]);

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
            $this->writer->writeContent($output);
        }

        if ($isCron !== false) {
            $this->finishCronRun($started, $queueEntry->foreignKeyID, $cacheHits, $cacheMisses);
        } else {
            require \PFAD_ROOT . \PFAD_INCLUDES . 'profiler_inc.php';
            $cb = new AsyncCallback();
            $cb->setExportID($this->model->getId())
                ->setTasksExecuted($this->queue->tasksExecuted)
                ->setQueueID($this->queue->jobQueueID)
                ->setProductCount($max)
                ->setLastProductID($this->queue->lastProductID)
                ->setIsFinished(false)
                ->setIsFirst($this->queue->tasksExecuted === 0)
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
        $this->db->delete('texportqueue', 'kExportqueue', $this->queue->foreignKeyID);

        $this->writer->writeFooter();
        if ($this->writer->finish()) {
            // Versucht (falls so eingestellt) die erstellte Exportdatei in mehrere Dateien zu splitten
            try {
                $this->writer->split();
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
                $this->syncReturn($cb);
            }
        }
    }

    /**
     * @param AsyncCallback $cb
     * @param bool          $isAsync
     */
    private function finishRun(AsyncCallback $cb, bool $isAsync): void
    {
        $this->writer->close();
        $this->db->queryPrepared(
            'UPDATE texportqueue SET
                nLimit_n       = nLimit_n + :nLimitM,
                nLastArticleID = :nLastArticleID
                WHERE kExportqueue = :kExportqueue',
            [
                'nLimitM'        => $this->queue->taskLimit,
                'nLastArticleID' => $this->queue->lastProductID,
                'kExportqueue'   => $this->queue->jobQueueID,
            ]
        );
        if ($isAsync) {
            $cb->output();
        } else {
            $this->syncContinue($cb);
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
            $this->writer->deleteOldExports();
            $this->writer->writeFooter();
            $this->writer->finish();
            $this->writer->split();
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
            . ' with caching ' . (($this->getCache()->isActive() && $this->model->getUseCache())
                ? 'enabled'
                : 'disabled'));
        $loader = PluginHelper::getLoaderByPluginID($this->model->getPluginID(), $this->db);
        try {
            $oPlugin = $loader->init($this->model->getPluginID());
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            $this->quit($e->getMessage());

            return false;
        }
        if ($oPlugin->getState() !== State::ACTIVATED) {
            $this->quit('Plugin disabled');
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
        global $started;
        include $oPlugin->getPaths()->getExportPath()
            . \str_replace(\PLUGIN_EXPORTFORMAT_CONTENTFILE, '', $this->model->getContent());
        if ($isAsync) {
            $this->model->setDateLastCreated(new DateTime());
            $this->model->save(['dateLastCreated']);
            exit;
        }

        return $started ?? false;
    }

    /**
     * @param string|null $error
     */
    private function quit(string $error = null): void
    {
        if (Request::getVar('back') !== 'admin') {
            return;
        }
        $cb = new AsyncCallback();
        $cb->setProductCount(0);
        $cb->setError($error);
        $this->syncReturn($cb);
        exit;
    }
}
