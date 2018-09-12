<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Items;

/**
 * Class AbstractItem
 * @package Sitemap\Items
 */
abstract class AbstractItem implements ItemInterface
{
    /**
     * @var string|null
     */
    protected $lastModificationTime;

    /**
     * @var string|null
     */
    protected $changeFreq;

    /**
     * @var string|null
     */
    protected $image;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var string|null
     */
    protected $priority;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $baseURL;

    /**
     * @var string
     */
    protected $baseImageURL;

    /**
     * AbstractItem constructor.
     * @param array  $config
     * @param string $baseURL
     * @param string $baseImageURL
     */
    public function __construct(array $config, string $baseURL, string $baseImageURL)
    {
        $this->config       = $config;
        $this->baseURL      = $baseURL;
        $this->baseImageURL = $baseImageURL;
    }

    /**
     * @inheritdoc
     */
    public function getChangeFreq(): ?string
    {
        return $this->changeFreq;
    }

    /**
     * @inheritdoc
     */
    public function setChangeFreq(string $changeFreq): void
    {
        $this->changeFreq = $changeFreq;
    }

    /**
     * @inheritdoc
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @inheritdoc
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @inheritdoc
     */
    public function getLastModificationTime(): ?string
    {
        return $this->lastModificationTime;
    }

    /**
     * @inheritdoc
     */
    public function setLastModificationTime($time): void
    {
        $this->lastModificationTime = $time;
    }

    /**
     * @inheritdoc
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @inheritdoc
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @inheritdoc
     */
    public function getPriority(): ?string
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function setPriority(string $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @inheritdoc
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function generateImage(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function generateLocation(): void
    {
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        $res           = \get_object_vars($this);
        $res['config'] = '*truncated*';

        return $res;
    }
}
