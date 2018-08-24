<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class NotificationEntry
 */
class NotificationEntry
{
    /**
     * None
     */
    const TYPE_NONE = -1;

    /**
     * Information type
     */
    const TYPE_INFO = 0;

    /**
     * Warning type
     */
    const TYPE_WARNING = 1;

    /**
     * Error type
     */
    const TYPE_DANGER = 2;

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
     * @param int $type
     * @param string $title
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
    public function getPluginId()
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle()
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
    public function getDescription()
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
     * @return boolean
     */
    public function hasDescription(): bool
    {
        return $this->description !== null && strlen($this->description) > 0;
    }

    /**
     * @return string|null
     */
    public function getUrl()
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
        return $this->url !== null && strlen($this->url) > 0;
    }
}
