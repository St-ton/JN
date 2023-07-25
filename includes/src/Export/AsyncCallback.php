<?php declare(strict_types=1);

namespace JTL\Export;

use DateTime;
use JTL\Router\Route;
use JTL\Shop;
use stdClass;

/**
 * Class AsyncCallback
 * @package JTL\Export
 */
class AsyncCallback
{
    /**
     * @var int
     */
    private int $exportID = 0;

    /**
     * @var int
     */
    private int $queueID = 0;

    /**
     * @var int
     */
    private int $productCount = 0;

    /**
     * @var int
     */
    private int $tasksExecuted = 0;

    /**
     * @var int
     */
    private int $lastProductID = 0;

    /**
     * @var bool
     */
    private bool $isFinished = false;

    /**
     * @var bool
     */
    private bool $isFirst = false;

    /**
     * @var int
     */
    private int $cacheHits = 0;

    /**
     * @var int
     */
    private int $cacheMisses = 0;

    /**
     * @var string
     */
    private string $url;

    /**
     * @var string|null
     */
    private ?string $error = null;

    /**
     * AsyncCallback constructor.
     */
    public function __construct()
    {
        $this->url = Shop::getAdminURL() . '/' . Route::EXPORT_START;
    }

    /**
     * @return void
     * @throws \JsonException
     */
    public function output(): void
    {
        $callback                 = new stdClass();
        $callback->kExportformat  = $this->getExportID();
        $callback->kExportqueue   = $this->getQueueID();
        $callback->nMax           = $this->getProductCount();
        $callback->nCurrent       = $this->getTasksExecuted();
        $callback->nLastArticleID = $this->getLastProductID();
        $callback->bFinished      = $this->isFinished();
        $callback->bFirst         = $this->isFirst() || $this->getTasksExecuted() === 0;
        $callback->cURL           = $this->getUrl();
        $callback->cacheMisses    = $this->getCacheMisses();
        $callback->cacheHits      = $this->getCacheHits();
        $callback->lastCreated    = (new DateTime())->format('Y-m-d H:i:s');
        $callback->errorMessage   = $this->getError() ?? '';

        echo \json_encode($callback, \JSON_THROW_ON_ERROR);
    }

    /**
     * @return int
     */
    public function getExportID(): int
    {
        return $this->exportID;
    }

    /**
     * @param int $exportID
     * @return AsyncCallback
     */
    public function setExportID(int $exportID): AsyncCallback
    {
        $this->exportID = $exportID;

        return $this;
    }

    /**
     * @return int
     */
    public function getQueueID(): int
    {
        return $this->queueID;
    }

    /**
     * @param int $queueID
     * @return AsyncCallback
     */
    public function setQueueID(int $queueID): AsyncCallback
    {
        $this->queueID = $queueID;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductCount(): int
    {
        return $this->productCount;
    }

    /**
     * @param int $productCount
     * @return AsyncCallback
     */
    public function setProductCount(int $productCount): AsyncCallback
    {
        $this->productCount = $productCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getTasksExecuted(): int
    {
        return $this->tasksExecuted;
    }

    /**
     * @param int $tasksExecuted
     * @return AsyncCallback
     */
    public function setTasksExecuted(int $tasksExecuted): AsyncCallback
    {
        $this->tasksExecuted = $tasksExecuted;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastProductID(): int
    {
        return $this->lastProductID;
    }

    /**
     * @param int $lastProductID
     * @return AsyncCallback
     */
    public function setLastProductID(int $lastProductID): AsyncCallback
    {
        $this->lastProductID = $lastProductID;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * @param bool $isFinished
     * @return AsyncCallback
     */
    public function setIsFinished(bool $isFinished): AsyncCallback
    {
        $this->isFinished = $isFinished;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFirst(): bool
    {
        return $this->isFirst;
    }

    /**
     * @param bool $isFirst
     * @return AsyncCallback
     */
    public function setIsFirst(bool $isFirst): AsyncCallback
    {
        $this->isFirst = $isFirst;

        return $this;
    }

    /**
     * @return int
     */
    public function getCacheHits(): int
    {
        return $this->cacheHits;
    }

    /**
     * @param int $cacheHits
     * @return AsyncCallback
     */
    public function setCacheHits(int $cacheHits): AsyncCallback
    {
        $this->cacheHits = $cacheHits;

        return $this;
    }

    /**
     * @return int
     */
    public function getCacheMisses(): int
    {
        return $this->cacheMisses;
    }

    /**
     * @param int $cacheMisses
     * @return AsyncCallback
     */
    public function setCacheMisses(int $cacheMisses): AsyncCallback
    {
        $this->cacheMisses = $cacheMisses;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return AsyncCallback
     */
    public function setUrl(string $url): AsyncCallback
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param string|null $error
     * @return AsyncCallback
     */
    public function setError(?string $error): AsyncCallback
    {
        $this->error = $error;

        return $this;
    }
}
