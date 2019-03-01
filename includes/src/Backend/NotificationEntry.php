<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend;

/**
 * Class NotificationEntry
 * @package Backend
 */
class NotificationEntry
{
    /**
     * None
     */
    public const TYPE_NONE = -1;

    /**
     * Information type
     */
    public const TYPE_INFO = 0;

    /**
     * Warning type
     */
    public const TYPE_WARNING = 1;

    /**
     * Error type
     */
    public const TYPE_DANGER = 2;

    /**
     * @var string
     */
    protected $pluginId;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $url;

    /**
     * NotificationEntry constructor.
     * @param int         $type
     * @param string      $title
     * @param null|string $description
     * @param null|string $url
     */
    public function __construct($type, $title, $description = null, $url = null)
    {
        $this->setType($type)
            ->setTitle($title)
            ->setDescription($description)
            ->setUrl($url);
    }

    /**
     * @return string|null
     */
    public function getPluginId(): ?string
    {
        return $this->pluginId;
    }

    /**
     * @param string $pluginId
     * @return $this
     */
    public function setPluginId($pluginId): self
    {
        $this->pluginId = $pluginId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDescription(): bool
    {
        return $this->description !== null && \mb_strlen($this->description) > 0;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasUrl(): bool
    {
        return $this->url !== null && \mb_strlen($this->url) > 0;
    }
}
