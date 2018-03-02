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
     * @return string
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * @param string $pluginId
     * @return $this
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasDescription()
    {
        return $this->description !== null && strlen($this->description) > 0;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasUrl()
    {
        return $this->url !== null && strlen($this->url) > 0;
    }
}
