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
     * AbstractItem constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
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
}
